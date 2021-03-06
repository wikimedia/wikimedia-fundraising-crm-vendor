<?php
namespace SmashPig\Core\QueueConsumers;

use Exception;
use InvalidArgumentException;
use PHPQueue\Exception\JsonException;
use PHPQueue\Interfaces\AtomicReadBuffer;

use SmashPig\Core\Context;
use SmashPig\Core\DataStores\DamagedDatabase;
use SmashPig\Core\DataStores\QueueWrapper;
use SmashPig\Core\Logging\Logger;
use SmashPig\Core\RetryableException;
use SmashPig\Core\UtcDate;

/**
 * Facilitates guaranteed message processing using PHPQueue's AtomicReadBuffer
 * interface. Exceptions in the processing callback will cause the message to
 * be sent to a damaged message datastore.
 */
abstract class BaseQueueConsumer {

	/**
	 * @var AtomicReadBuffer
	 */
	protected $backend;

	protected $queueName;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var DamagedDatabase
	 */
	protected $damagedDb;

	protected $timeLimit = 0;

	protected $messageLimit = 0;

	/**
	 * Do something with the message popped from the queue. Return value is
	 * ignored, and exceptions will be caught and handled by handleError.
	 *
	 * @param array $message
	 */
	abstract public function processMessage( array $message );

	/**
	 * Gets a fresh QueueConsumer
	 *
	 * @param string $queueName key of queue configured in data-store, must
	 *  implement @see PHPQueue\Interfaces\AtomicReadBuffer.
	 * @param int $timeLimit max number of seconds to loop, 0 for no limit
	 * @param int $messageLimit max number of messages to process, 0 for all
	 * @throws \SmashPig\Core\ConfigurationKeyException
	 */
	public function __construct(
		string $queueName,
		int $timeLimit = 0,
		int $messageLimit = 0
	) {
		if ( !is_numeric( $timeLimit ) ) {
			throw new InvalidArgumentException( 'timeLimit must be numeric' );
		}
		if ( !is_numeric( $messageLimit ) ) {
			throw new InvalidArgumentException( 'messageLimit must be numeric' );
		}

		$this->queueName = $queueName;
		$this->timeLimit = intval( $timeLimit );
		$this->messageLimit = intval( $messageLimit );

		$this->backend = QueueWrapper::getQueue( $queueName );

		if ( !$this->backend instanceof AtomicReadBuffer ) {
			throw new InvalidArgumentException(
				"Queue $queueName is not an AtomicReadBuffer"
			);
		}

		$this->damagedDb = DamagedDatabase::get();
	}

	/**
	 * Dequeue and process messages until time limit or message limit is
	 * reached, or till queue is empty.
	 *
	 * @return int number of messages processed
	 * @throws Exception
	 */
	public function dequeueMessages(): int {
		$startTime = time();
		$processed = 0;
		$realCallback = [ $this, 'processMessageWithErrorHandling' ];
		do {
			try {
				$data = $this->backend->popAtomic( $realCallback );
				if ( $data !== null ) {
					$processed++;
				}
			} catch ( JsonException $ex ) {
				$data = false;
				$this->sendToDamagedStore( null, $ex );
			}
			$timeOk = $this->timeLimit === 0 || time() <= $startTime + $this->timeLimit;
			$countOk = $this->messageLimit === 0 || $processed < $this->messageLimit;

			$debugMessages = [];
			if ( $data === null ) {
				$debugMessages[] = 'Queue is empty.';
			} elseif ( !$timeOk ) {
				$debugMessages[] = "Time limit ($this->timeLimit) is elapsed.";
			} elseif ( !$countOk ) {
				$debugMessages[] = "Message limit ($this->messageLimit) is reached.";
			}
			if ( !empty( $debugMessages ) ) {
				Logger::debug( implode( ' ', $debugMessages ) );
			}
		}
		while ( $timeOk && $countOk && $data !== null );
		return $processed;
	}

	/**
	 * Call the concrete processMessage function and handle any errors that
	 * may arise.
	 *
	 * @param array $message
	 */
	public function processMessageWithErrorHandling( array $message ) {
		try {
			$this->processMessage( $message );
		} catch ( Exception $ex ) {
			$this->handleError( $message, $ex );
		}
	}

	/**
	 * Using an AtomicReadBuffer implementation for the backend means that
	 * if this throws an exception, the message will remain on the queue.
	 *
	 * @param array $message
	 * @param Exception $ex
	 * @throws \SmashPig\Core\ConfigurationKeyException
	 */
	protected function handleError( array $message, Exception $ex ) {
		if ( $ex instanceof RetryableException ) {
			$now = UtcDate::getUtcTimestamp();
			$config = Context::get()->getGlobalConfiguration();

			if ( !isset( $message['source_enqueued_time'] ) ) {
				$message['source_enqueued_time'] = UtcDate::getUtcTimestamp();
			}
			$expirationDate = $message['source_enqueued_time'] +
				$config->val( 'requeue-max-age' );

			if ( $now < $expirationDate ) {
				$retryDate = $now + $config->val( 'requeue-delay' );
				$this->sendToDamagedStore( $message, $ex, $retryDate );
				return;
			}
		}
		$this->sendToDamagedStore( $message, $ex );
	}

	/**
	 * @param array $message The data
	 * @param Exception $ex The problem
	 * @param int|null $retryDate If provided, retry after this timestamp
	 * @return int ID of message in damaged database
	 * @throws \SmashPig\Core\DataStores\DataStoreException
	 */
	protected function sendToDamagedStore(
		array $message, Exception $ex, $retryDate = null
	) {
		if ( $retryDate ) {
			Logger::notice(
				'Message not fully baked. Sticking it back in the oven, to ' .
				"retry at $retryDate",
				$message
			);
		} else {
			Logger::error(
				'Error processing message, moving to damaged store.',
				$message,
				$ex
			);
		}
		return $this->damagedDb->storeMessage(
			$message,
			$this->queueName,
			$ex->getMessage(),
			$ex->getTraceAsString(),
			$retryDate
		);
	}
}
