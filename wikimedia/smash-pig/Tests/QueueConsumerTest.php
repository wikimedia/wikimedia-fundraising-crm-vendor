<?php

namespace SmashPig\Tests;

use PHPQueue\Interfaces\FifoQueueStore;
use SmashPig\Core\DataStores\QueueConsumer;

class QueueConsumerTest extends BaseSmashPigUnitTestCase {

	/**
	 * @var FifoQueueStore
	 */
	protected $queue;

	public function setUp() {
		parent::setUp();
		$this->setConfig( 'default', __DIR__ . '/data/config_queue.yaml' );
		$this->queue = QueueConsumer::getQueue( 'test' );
		$this->queue->createTable( 'test' );
	}

	public function testEmptyQueue() {
		$noOp = function( $unused ) {};
		$consumer = new QueueConsumer( 'test', $noOp );
		$count = $consumer->dequeueMessages();
		$this->assertEquals( 0, $count, 'Should report 0 messages processed' );
	}

	public function testOneMessage() {
		$processed = array();
		$cb = function( $message ) use ( &$processed ) {
			$processed[] = $message;
		};
		$consumer = new QueueConsumer( 'test', $cb );
		$payload = array(
			'wednesday' => 'addams',
			'spookiness' => mt_rand(),
		);
		$this->queue->push( $payload );
		$count = $consumer->dequeueMessages();
		$this->assertEquals( 1, $count, 'Should report 1 message processed' );
		$this->assertEquals( array( $payload ), $processed, 'Bad message' );
		$this->assertNull( $this->queue->pop(),
			'Should delete message when processing is successful'
		);
	}

	public function testRollBack() {
		$payload = array(
			'uncle' => 'fester',
			'watts' => mt_rand(),
		);
		$self = $this;
		$ran = false;
		$cb = function( $message ) use ( &$ran, $payload, $self ) {
			$self->assertEquals( $message, $payload );
			$ran = true;
			throw new \Exception( 'kaboom!' );
		};
		$consumer = new QueueConsumer( 'test', $cb );
		$this->queue->push( $payload );
		try {
			$consumer->dequeueMessages();
			$this->fail( 'Exception should have bubbled up' );
		} catch ( \Exception $ex ) {
			$this->assertEquals( 'kaboom!', $ex->getMessage(), 'Exception mutated' );
		}
		$this->assertTrue( $ran, 'Callback was not called' );
		$this->assertEquals(
			$payload,
			$this->queue->pop(),
			'Should not delete message when exception is thrown'
		);
	}

	public function testDamagedQueue() {
		$damagedQueue = QueueConsumer::getQueue( 'damaged' );
		$damagedQueue->createTable('damaged'); // FIXME: should not need

		$payload = array(
			'cousin' => 'itt',
			'kookiness' => mt_rand(),
		);
		$self = $this;
		$ran = false;
		$cb = function( $message ) use ( &$ran, $payload, $self ) {
			$self->assertEquals( $message, $payload );
			$ran = true;
			throw new \Exception( 'kaboom!' );
		};

		$consumer = new QueueConsumer( 'test', $cb, 0, 0, 'damaged' );

		$this->queue->push( $payload );
		try {
			$consumer->dequeueMessages();
		} catch ( \Exception $ex ) {
			$this->fail(
				'Exception should not have bubbled up: ' . $ex->getMessage()
			);
		}
		$this->assertTrue( $ran, 'Callback was not called' );
		$this->assertEquals(
			$payload,
			$damagedQueue->pop(),
			'Should move message to damaged queue when exception is thrown'
		);
		$this->assertNull(
			$this->queue->pop(),
			'Should delete message on exception when damaged queue exists'
		);
	}

	public function testMessageLimit() {
		$messages = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$message = array(
				'box' => 'thing' . $i,
				'creepiness' => mt_rand(),
			);
			$messages[] = $message;
			$this->queue->push( $message );
		}
		$processedMessages = array();
		$callback = function( $message ) use ( &$processedMessages ) {
			$processedMessages[] = $message;
		};
		// Should work when you pass in the limits as strings.
		$consumer = new QueueConsumer( 'test', $callback, 0, '3' );
		$count = $consumer->dequeueMessages();
		$this->assertEquals( 3, $count, 'dequeueMessages returned wrong count' );
		$this->assertEquals( 3, count( $processedMessages ), 'Called callback wrong number of times' );

		for ( $i = 0; $i < 3; $i++ ) {
			$this->assertEquals( $messages[$i], $processedMessages[$i], 'Message mutated' );
		}
		$this->assertEquals(
			$messages[3],
			$this->queue->pop(),
			'Messed with too many messages'
		);
	}

	public function testKeepRunningOnDamage() {
		$damagedQueue = QueueConsumer::getQueue( 'damaged' );
		$damagedQueue->createTable( 'damaged' ); // FIXME: should not need

		$messages = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$message = array(
				'box' => 'thing' . $i,
				'creepiness' => mt_rand(),
			);
			$messages[] = $message;
			$this->queue->push( $message );
		}
		$processedMessages = array();
		$cb = function( $message ) use ( &$processedMessages ) {
			$processedMessages[] = $message;
			throw new \Exception( 'kaboom!' );
		};

		$consumer = new QueueConsumer( 'test', $cb, 0, 3, 'damaged' );
		$count = 0;
		try {
			$count = $consumer->dequeueMessages();
		} catch ( \Exception $ex ) {
			$this->fail(
				'Exception should not have bubbled up: ' . $ex->getMessage()
			);
		}
		$this->assertEquals( 3, $count, 'dequeueMessages returned wrong count' );
		$this->assertEquals( 3, count( $processedMessages ), 'Called callback wrong number of times' );

		for ( $i = 0; $i < 3; $i++ ) {
			$this->assertEquals( $messages[$i], $processedMessages[$i], 'Message mutated' );
			$this->assertEquals(
				$messages[$i],
				$damagedQueue->pop(),
				'Should move message to damaged queue when exception is thrown'
			);
		}
		$this->assertEquals(
			$messages[3],
			$this->queue->pop(),
			'message 4 should be at the head of the queue'
		);
	}

}
