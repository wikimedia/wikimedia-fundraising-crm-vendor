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
use Psr\Log\LogLevel;

/**
 * @group Fundraising
 * @group DonationInterface
 * @group DIIntegration
 */
class DonationInterface_IntegrationTest extends DonationInterfaceTestCase {

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	public function __construct( $name = null, array $data = array(), $dataName = '' ) {
		$adapterclass = TESTS_ADAPTER_DEFAULT;
		$this->testAdapterClass = $adapterclass;

		parent::__construct( $name, $data, $dataName );
	}

	public function setUp() {
		global $wgGlobalCollectGatewayHtmlFormDir, $wgPaypalGatewayHtmlFormDir;

		parent::setUp();

		$this->setMwGlobals( array(
			'wgGlobalCollectGatewayEnabled' => true,
			'wgPaypalGatewayEnabled' => true,
			'wgDonationInterfaceAllowedHtmlForms' => array(
				'cc-vmad' => array(
					'file' => $wgGlobalCollectGatewayHtmlFormDir . '/cc/cc-vmad.html',
					'gateway' => 'globalcollect',
					'payment_methods' => array ( 'cc' => array ( 'visa', 'mc', 'amex', 'discover' ) ),
					'countries' => array (
						'+' => array ( 'US', ),
					),
				),
				'paypal' => array(
					'file' => $wgPaypalGatewayHtmlFormDir . '/paypal.html',
					'gateway' => 'paypal',
					'payment_methods' => array ( 'paypal' => 'ALL' ),
				),
			),
		) );
	}

	//this is meant to simulate a user choosing paypal, then going back and choosing GC.
	public function testBackClickPayPalToGC() {
		$this->testAdapterClass = 'TestingPaypalAdapter';
		$options = $this->getDonorTestData( 'US' );

		$options['payment_method'] = 'paypal';
		$gateway = $this->getFreshGatewayObject( $options );
		$gateway->do_transaction( 'Donate' );

		//check to see that we have a numAttempt and form set in the session
		$this->assertEquals( 'paypal', $_SESSION['PaymentForms'][0], "Paypal didn't load its form." );
		$this->assertEquals( '1', $_SESSION['numAttempt'], "We failed to record the initial paypal attempt in the session" );
		//now, get GC.
		$this->testAdapterClass = 'TestingGlobalCollectAdapter';
		$options['payment_method'] = 'cc';
		$gateway = $this->getFreshGatewayObject( $options );
		$gateway->do_transaction( 'INSERT_ORDERWITHPAYMENT' );

		$ffname = $gateway->getData_Unstaged_Escaped( 'ffname' );
		$this->assertEquals( 'cc-vmad', $ffname, "GC did not load the expected form." );

		$errors = '';
		if ( array_key_exists( LogLevel::ERROR, $this->testLogger->messages ) ) {
			foreach ( $this->testLogger->messages[LogLevel::ERROR] as $msg ) {
				$errors += "$msg\n";
			}
		}
		$this->assertEmpty( $errors, "The gateway error log had the following message(s):\n" . $errors );
	}

}
