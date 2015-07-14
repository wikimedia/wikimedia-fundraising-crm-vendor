<?php
/**
 * Wikimedia Foundation
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 */

/**
 * @group       DonationInterface
 * @group       QueueHandling
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class DonationQueueTest extends DonationInterfaceTestCase {
	protected $transaction;
	protected $queue_name;
	protected $expected_message;

	public function setUp() {
		parent::setUp();

		$this->queue_name = 'test-' . mt_rand();

		TestingGlobalcollectAdapter::clearGlobalsCache();
		// FIXME: I hope that made your hair stand on end.

		$this->setMwGlobals( array(
			'wgDonationInterfaceEnableQueue' => true,
			'wgDonationInterfaceDefaultQueueServer' => array(
				'type' => 'TestingQueue',
			),
			'wgDonationInterfaceQueues' => array(
				$this->queue_name => array(),
			),
		) );

		$this->transaction = array(
			'amount' => '1.24',
			'city' => 'Dunburger',
			'contribution_tracking_id' => mt_rand(),
			// FIXME: err, we're cheating normalization here.
			'correlation-id' => 'testgateway-' . mt_rand(),
			'country' => 'US',
			'currency_code' => 'USD',
			'date' => time(),
			'email' => 'nobody@wikimedia.org',
			'fname' => 'Jen',
			'gateway_account' => 'default',
			'gateway' => 'testgateway',
			'gateway_txn_id' => mt_rand(),
			'language' => 'en',
			'lname' => 'Russ',
			'payment_method' => 'cc',
			'payment_submethod' => 'visa',
			'php-message-class' => 'SmashPig\CrmLink\Messages\DonationInterfaceMessage',
			'referrer' => 'http://localhost.net/Ref',
			'response' => 'Gateway response something',
			'state' => 'AK',
			'street' => '1 Fake St.',
			'user_ip' => '127.0.0.1',
			'utm_source' => 'testing',
			'zip' => '12345',
		);

		$this->expected_message = array(
			'contribution_tracking_id' => $this->transaction['contribution_tracking_id'],
			'utm_source' => 'testing',
			'language' => 'en',
			'referrer' => 'http://localhost.net/Ref',
			'email' => 'nobody@wikimedia.org',
			'first_name' => 'Jen',
			'last_name' => 'Russ',
			'street_address' => '1 Fake St.',
			'city' => 'Dunburger',
			'state_province' => 'AK',
			'country' => 'US',
			'postal_code' => '12345',
			'gateway' => 'testgateway',
			'gateway_account' => 'default',
			'gateway_txn_id' => $this->transaction['gateway_txn_id'],
			'payment_method' => 'cc',
			'payment_submethod' => 'visa',
			'response' => 'Gateway response something',
			'currency' => 'USD',
			'fee' => '0',
			'gross' => '1.24',
			'user_ip' => '127.0.0.1',
			'date' => (int)$this->transaction['date'],
		);
	}

	public function tearDown() {
		// Clear static variables.
		TestingQueue::clearAll();

		parent::tearDown();
	}

	public function testPushMessage() {
		DonationQueue::instance()->push( $this->transaction, $this->queue_name );

		$this->assertEquals( $this->expected_message,
			DonationQueue::instance()->pop( $this->queue_name ) );
	}

	/**
	 * After pushing 2, pop should return the first.
	 */
	public function testIsFifoQueue() {
		DonationQueue::instance()->push( $this->transaction, $this->queue_name );

		$transaction2 = $this->transaction;
		$transaction2['correlation-id'] = mt_rand();

		$this->assertEquals( $this->expected_message,
			DonationQueue::instance()->pop( $this->queue_name ) );
	}

	public function testSetMessage() {
		DonationQueue::instance()->set( $this->transaction['correlation-id'],
			$this->transaction, $this->queue_name );

		$this->assertEquals( $this->expected_message,
			DonationQueue::instance()->get(
				$this->transaction['correlation-id'], $this->queue_name ) );
	}

	public function testDeleteMessage() {
		DonationQueue::instance()->set( $this->transaction['correlation-id'],
			$this->transaction, $this->queue_name );
		$this->assertEquals( $this->expected_message,
			DonationQueue::instance()->get(
				$this->transaction['correlation-id'], $this->queue_name ) );

		DonationQueue::instance()->delete(
			$this->transaction['correlation-id'], $this->queue_name );

		$this->assertNull(
			DonationQueue::instance()->get(
				$this->transaction['correlation-id'], $this->queue_name ) );
	}
}
