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
 *
 * @group Fundraising
 * @group DonationInterface
 * @group Ingenico
 * @group Recurring
 */
class DonationInterface_Adapter_Ingenico_RecurringTest extends BaseIngenicoTestCase {

	public function setUp() {
		parent::setUp();
		$this->markTestSkipped( 'Recurring not implemented' );
	}

	/**
	 * Can make a recurring payment
	 *
	 * @covers IngenicoAdapter::transactionRecurring_Charge
	 */
	public function testRecurringCharge() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( 'recurring-OK' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertTrue( $result->getCommunicationStatus() );
		$this->assertRegExp( '/SET_PAYMENT/', $result->getRawResponse() );
	}

	/**
	 * Can make a recurring payment
	 *
	 * @covers IngenicoAdapter::transactionRecurring_Charge
	 */
	public function testDeclinedRecurringCharge() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( 'recurring-declined' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertRegExp(
			'/GET_ORDERSTATUS/',
			$result->getRawResponse(),
			'Stopped after GET_ORDERSTATUS.'
		);
		$this->assertEquals(
			2,
			count( $gateway->curled ),
			'Expected 2 API calls'
		);
		$this->assertEquals( FinalStatus::FAILED, $gateway->getFinalStatus() );
	}

	/**
	 * Throw errors if the payment is incomplete
	 *
	 * @covers IngenicoAdapter::transactionRecurring_Charge
	 */
	public function testRecurringTimeout() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( 'recurring-timeout' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertFalse( $result->getCommunicationStatus() );
		$this->assertRegExp( '/GET_ORDERSTATUS/', $result->getRawResponse() );
		// FIXME: This is a little funky--the transaction is actually pending-poke.
		$this->assertEquals( FinalStatus::FAILED, $gateway->getFinalStatus() );
	}

	/**
	 * Can resume a recurring payment
	 *
	 * @covers IngenicoAdapter::transactionRecurring_Charge
	 */
	public function testRecurringResume() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( 'recurring-resume' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertTrue( $result->getCommunicationStatus() );
		$this->assertRegExp( '/SET_PAYMENT/', $result->getRawResponse() );
	}
}