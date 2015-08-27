<?php

class DonationQueue {
	protected static $instance;

	protected function __construct() {
	}

	/**
	 * Singleton entrypoint
	 *
	 * @return DonationQueue
	 */
	public static function instance() {
		if ( !self::$instance ) {
			self::$instance = new DonationQueue();
		}
		return self::$instance;
	}

	public function push( $transaction, $queue ) {
		// TODO: This should be checked once, at a higher level.
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		$properties = $this->buildHeaders( $transaction );
		$message = $this->buildBody( $transaction );
		$this->newBackend( $queue )->push( $message, $properties );
	}

	public function pop( $queue ) {
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		$backend = $this->newBackend( $queue );

		return $backend->pop();
	}

	public function peek( $queue ) {
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		$backend = $this->newBackend( $queue );

		return $backend->peek();
	}

	public function set( $correlationId, $transaction, $queue ) {
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		$properties = $this->buildHeaders( $transaction );
		$message = $this->buildBody( $transaction );
		$this->newBackend( $queue )->set( $correlationId, $message, $properties );
	}

	public function get( $correlationId, $queue ) {
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		return $this->newBackend( $queue )->get( $correlationId );
	}

	public function delete( $correlationId, $queue ) {
		if ( !GatewayAdapter::getGlobal( 'EnableQueue' ) ) {
			return;
		}
		$this->newBackend( $queue )->clear( $correlationId );
	}

	/**
	 * Build message headers from given donation data array
	 *
	 * @param array $transaction
	 * @return array
	 */
	protected function buildHeaders( $transaction ) {
		global $IP;

		// TODO: Core helper function.
		static $sourceRevision = null;
		if ( !$sourceRevision ) {
			$versionStampPath = "$IP/.version-stamp";
			if ( file_exists( $versionStampPath ) ) {
				$sourceRevision = trim( file_get_contents( $versionStampPath ) );
			} else {
				$sourceRevision = 'unknown';
			}
		}

		// Create the message and associated properties
		$properties = array(
			'gateway' => $transaction['gateway'],
			// TODO: Move 'persistent' to PHPQueue backend default.
			'persistent' => 'true',
			'source_enqueued_time' => time(),
			'source_host' => WmfFramework::getHostname(),
			'source_name' => 'DonationInterface',
			'source_run_id' => getmypid(),
			'source_type' => 'payments',
			'source_version' => $sourceRevision,
		);
		if ( isset( $transaction['correlation-id'] ) ) {
			$properties['correlation-id'] = $transaction['correlation-id'];
		} else {
			$properties['correlation-id'] = $transaction['gateway'] . '-' . $transaction['gateway_txn_id'];
		}
		// FIXME: In a better world, we'd actually be using this class to
		// determine what kind of normalization is required.
		if ( isset( $transaction['php-message-class'] ) ) {
			$properties['php-message-class'] = $transaction['php-message-class'];
		}

		return $properties;
	}

	/**
	 * Build a body string, given a donation data array
	 *
	 * @param array $transaction
	 *
	 * @return array Message body.  Note that we aren't json_encoding here, cos
	 * PHPQueue expects an array.
	 */
	protected function buildBody( $transaction ) {
		if ( array_key_exists( 'freeform', $transaction ) && $transaction['freeform'] ) {
			$data = $transaction;
		} else {
			// Assume anything else is a regular donation.
			$data = $this->buildTransactionMessage( $transaction );
		}
		return $data;
	}

	/**
	 * Construct a new backend queue object
	 *
	 * Build the configuration for a named queue, and instantiate an appropriate backend object.
	 *
	 * @param string $name Queue identifier used for config lookup.
	 * @param array $options Additional values to pass to the backend's constructor.
	 * @return \PHPQueue\Interfaces\FifoQueueStore and \PHPQueue\Interfaces\KeyValueStore
	 */
	protected function newBackend( $name, $options = array() ) {
		global $wgDonationInterfaceDefaultQueueServer, $wgDonationInterfaceQueues;

		// Default to the unmapped queue name.
		$queueNameDefaults = array(
			'queue' => $name,
		);

		// Use queue-specific customizations if available, but overridden by any
		// $options passed to this function.
		if ( array_key_exists( $name, $wgDonationInterfaceQueues ) ) {
			$options = array_merge(
				$wgDonationInterfaceQueues[$name],
				$options
			);
		}

		// Merge config options, from least to greatest precedence.
		$serverConfig = array_merge(
			$queueNameDefaults,
			$wgDonationInterfaceDefaultQueueServer,
			$options
		);

		// What is this?  Make one.
		$className = $serverConfig['type'];
		if ( !class_exists( $className ) ) {
			throw new RuntimeException( "Queue backend class not found: [$className]" );
		}
		return new $className( $serverConfig );
	}

	/**
	 * Assign correct values to the array of data to be sent to the ActiveMQ server
	 * TODO: Probably something else. I don't like the way this works and neither do you.
	 *
	 * Older notes follow:
	 * Currency in receiving module has currency set to USD, should take passed variable for these
	 * PAssed both ISO and country code, no need to look up
	 * 'gateway' = globalcollect, e.g.
	 * 'date' is sent as $date("r") so it can be translated with strtotime like Paypal transactions (correct?)
	 * Processor txn ID sent in the transaction response is assigned to 'gateway_txn_id' (PNREF)
	 * Order ID (generated with transaction) is assigned to 'contribution_tracking_id'?
	 * Response from processor is assigned to 'response'
	 */
	protected function buildTransactionMessage( $transaction ) {
		// specifically designed to match the CiviCRM API that will handle it
		// edit this array to include/ignore transaction data sent to the server
		$message = array(
			'contribution_tracking_id' => $transaction['contribution_tracking_id'],
			'country' => $transaction['country'],
			//the following int casting fixes an issue that is more in Drupal/CiviCRM than here.
			//The code there should also be fixed.
			'date' => (int)$transaction['date'],
			'email' => $transaction['email'],
			'fee' => '0',
			'gateway_account' => $transaction['gateway_account'],
			'gateway' => $transaction['gateway'],
			'gateway_txn_id' => $transaction['gateway_txn_id'],
			'language' => $transaction['language'],
			'payment_method' => $transaction['payment_method'],
			'payment_submethod' => $transaction['payment_submethod'],
			'referrer' => $transaction['referrer'],
			'response' => $transaction['response'],
			'user_ip' => $transaction['user_ip'],
			'utm_source' => $transaction['utm_source'],
		);

		// We're using this mapping for optional fields, and to cheat on not
		// transforming messages a if they are processed through this function
		// multiple times.
		$optional_keys = array(
			'anonymous' => 'anonymous',
			'city' => 'city',
			'currency' => 'currency_code',
			'first_name' => 'fname',
			'gross' => 'amount',
			'last_name' => 'lname',
			'optout' => 'optout',
			'recurring' => 'recurring',
			'state_province' => 'state',
			'street_address' => 'street',
			'supplemental_address_1' => 'street_supplemental',
			'utm_campaign' => 'utm_campaign',
			'utm_medium' => 'utm_medium',
			'postal_code' => 'zip',
		);
		foreach ( $optional_keys as $mkey => $tkey ) {
			if ( isset( $transaction[$tkey] ) ) {
				$message[$mkey] = $transaction[$tkey];
			} elseif ( isset( $transaction[$mkey] ) ) {
				// Just copy if it's already using the correct key.
				$message[$mkey] = $transaction[$mkey];
			}
		}

		return $message;
	}

	/**
	 * Called by the orphan rectifier to change a queue message back into a gateway
	 * transaction array, basically undoing the mappings from buildTransactionMessage.
	 *
	 * TODO: This shouldn't be necessary, see https://phabricator.wikimedia.org/T109819
	 *
	 * @param array $transaction Queue message
	 *
	 * @return array message with queue keys remapped to gateway keys
	 */
	public static function queueMessageToNormalized( $transaction ) {
		// For now, this function assumes that we have a complete queue message.

		$rekey = array(
			'currency' => 'currency_code',
			'first_name' => 'fname',
			'gross' => 'amount',
			'last_name' => 'lname',
			'postal_code' => 'zip',
			'state_province' => 'state',
			'street_address' => 'street',
			'supplemental_address_1' => 'street_supplemental',
		);

		foreach ( $rekey as $wire => $normal ){
			if ( isset( $transaction[$wire] ) ){
				$transaction[$normal] = $transaction[$wire];
				unset($transaction[$wire]);
			};
		}

		return $transaction;
	}
}
