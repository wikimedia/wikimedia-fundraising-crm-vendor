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
 */

/**
 * 
 * @group Fundraising
 * @group DonationInterface
 * @group PayPal
 */
class DonationInterface_Adapter_PayPal_Test extends DonationInterfaceTestCase {

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	public function __construct( $name = null, array $data = array(), $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		$this->testAdapterClass = 'TestingPaypalAdapter';
	}

	public function setUp() {
		global $wgPaypalGatewayHtmlFormDir;

		parent::setUp();

		$this->setMwGlobals( array(
			'wgPaypalGatewayEnabled' => true,
			'wgDonationInterfaceAllowedHtmlForms' => array(
				'paypal' => array(
					'file' => $wgPaypalGatewayHtmlFormDir . '/paypal.html',
					'gateway' => 'paypal',
					'payment_methods' => array('paypal' => 'ALL'),
				),
				'paypal-recurring' => array(
					'file' => $wgPaypalGatewayHtmlFormDir . '/paypal-recurring.html',
					'gateway' => 'paypal',
					'payment_methods' => array('paypal' => 'ALL'),
					'recurring',
				),
			),
		) );
	}

	public function tearDown() {
		TestingPaypalAdapter::$fakeGlobals = array();

		parent::tearDown();
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonate() {
		$init = $this->getDonorTestData();
		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_donations',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => $init['country'], //this works because it's a US donor...
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "Donate" transaction not constructing the expected redirect URL' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Paypal order_id is not null, and we shouldn't be generating one" );
	}

	/**
	 * Integration test to verify that the DonateRecurring transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonateRecurring() {
		$init = $this->getDonorTestData();
		$init['recurring'] = '1';
		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'a3' => $init['amount'], //obviously.
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_xclick-subscriptions',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => $init['country'], //this works because it's a US donor...
			't3' => 'M', //hard-coded in transaction definition
			'p3' => '1', //hard-coded in transaction definition
			'src' => '1', //hard-coded in transaction definition
			'srt' => $gateway->getGlobal( 'RecurringLength' ),
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "DonateRecurring" transaction not constructing the expected redirect URL' );
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonateXclick() {
		$init = $this->getDonorTestData();

		TestingPaypalAdapter::$fakeGlobals = array(
			'XclickCountries' => array( $init['country'] ),
		);

		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_xclick',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '1', //hard-coded in transaction definition
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
//			'lc' => $init['country'], //Apparently, this was removed from our implementation, because 'CN' is weird.
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
			'no_shipping' => '1', //hard-coded in transaction definition
		);

		$this->assertEquals( $expected, $res, 'Paypal "DonateXclick" transaction not constructing the expected redirect URL' );
	}

	/**
	 * Integration test to verify that the Paypal gateway redirects when validation is successful.
	 */
	function testRedirectFormOnValid() {
		$init = $this->getDonorTestData();
		$_SESSION['Donor'] = $init;

		$that = $this;
		$redirectTest = function( $location ) use ( $that, $init ) {
			parse_str( parse_url( $location, PHP_URL_QUERY ), $actual );
			$that->assertEquals( $init['amount'], $actual['amount'] );
		};
		$assertNodes = array(
			'headers' => array(
				'Location' => $redirectTest,
			)
		);

		$this->verifyFormOutput( 'PaypalGateway', $init, $assertNodes, false );
	}

	/**
	 * Integration test to verify that the Paypal gateway shows an error message when validation fails.
	 */
	function testShowFormOnError() {
		$init = $this->getDonorTestData();
		$init['amount'] = '-100.00';
		$_SESSION['Donor'] = $init;
		$errorMessage = wfMessage('donate_interface-error-msg-field-correction', wfMessage('donate_interface-error-msg-amount')->text())->text();
		$assertNodes = array(
			'mw-content-text' => array(
				'innerhtmlmatches' => "/.*$errorMessage.*/"
			)
		);

		$this->verifyFormOutput( 'PaypalGateway', $init, $assertNodes, false );
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected in Belgium for fr, de, and nl.
	 *
	 * @dataProvider belgiumLanguageProvider
	 */
	function testDoTransactionDonate_BE( $language ) {
		$init = $this->getDonorTestData( 'BE' );
		$init['language'] = $language;
		$this->setLanguage( $language );
		$gateway = $this->getFreshGatewayObject( $init );
		$donateText = wfMessage( 'donate_interface-donation-description' )->inLanguage( $language )->text();
		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => 'BE',
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_donations',
			'item_name' => $donateText,
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => $init['country'], //this works because it's a US donor...
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "Donate" transaction not constructing the expected redirect URL' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Paypal order_id is not null, and we shouldn't be generating one" );
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected
	 * in Canada for English and French
	 *
	 * @dataProvider belgiumLanguageProvider
	 */
	function testDoTransactionDonate_CA( $language ) {
		$init = $this->getDonorTestData( 'CA' );
		$init['language'] = $language;
		$this->setLanguage( $language );
		$gateway = $this->getFreshGatewayObject( $init );
		$donateText = wfMessage( 'donate_interface-donation-description' )->inLanguage( $language )->text();
		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => 'CAD',
			'country' => 'CA',
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_donations',
			'item_name' => $donateText,
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => 'CA',
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "Donate" transaction not constructing the expected redirect URL' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Paypal order_id is not null, and we shouldn't be generating one" );
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected in Italy
	 */
	function testDoTransactionDonate_IT() {
		$init = $this->getDonorTestData( 'IT' );
		$this->setLanguage( 'it' );
		$gateway = $this->getFreshGatewayObject( $init );
		$donateText = wfMessage( 'donate_interface-donation-description' )->inLanguage( 'it' )->text();
		$ret = $gateway->doPayment();
		parse_str( parse_url( $ret->getRedirect(), PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => 'IT',
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_donations',
			'item_name' => $donateText,
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => 'IT',
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "Donate" transaction not constructing the expected redirect URL' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Paypal order_id is not null, and we shouldn't be generating one" );
	}
}
