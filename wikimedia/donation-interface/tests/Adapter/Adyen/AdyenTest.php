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
 * @group Adyen
 */
class DonationInterface_Adapter_Adyen_Test extends DonationInterfaceTestCase {

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	public function __construct( $name = null, array $data = array(), $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		$this->testAdapterClass = 'TestingAdyenAdapter';
	}

	public function setUp() {
		parent::setUp();

		$this->setMwGlobals( array(
			'wgAdyenGatewayEnabled' => true,
		) );
	}

	/**
	 * Integration test to verify that the donate transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonate() {
		$init = $this->getDonorTestData();
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->do_transaction( 'donate' );
		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$ret = $exposed->buildRequestParams();

		$expected = array (
			'allowedMethods' => 'card',
			'billingAddress.street' => $init['street'],
			'billingAddress.city' => $init['city'],
			'billingAddress.postalCode' => $init['zip'],
			'billingAddress.stateOrProvince' => $init['state'],
			'billingAddress.country' => $init['country'],
			'billingAddressSig' => 'OPd7taLd7uNWt9Izhq210n9/z2c=',
			'billingAddressType' => 2,
			'currencyCode' => $init['currency_code'],
			'merchantAccount' => 'wikitest',
			'merchantReference' => $exposed->getData_Staged( 'order_id' ),
			'merchantSig' => $exposed->getData_Staged( 'hpp_signature' ),
			'paymentAmount' => ($init['amount']) * 100,
//			'sessionValidity' => '2014-03-09T19:41:50+00:00',	//commenting out, because this is a problem.
//			'shipBeforeDate' => $exposed->getData_Staged( 'expiration' ),	//this too.
			'skinCode' => 'testskin',
			'shopperLocale' => 'en',
			'shopperEmail' => 'nobody@wikimedia.org',
			'offset' => 51.5, //once we construct the FraudFiltersTestCase, it should land here.
		);

		//deal with problem keys.
		//@TODO: Refactor gateway so these are more testable
		$problems = array (
			'sessionValidity',
			'shipBeforeDate',
		);

		foreach ( $problems as $oneproblem ) {
			if ( isset( $ret[$oneproblem] ) ) {
				unset( $ret[$oneproblem] );
			}
		}

		$this->assertEquals( $expected, $ret, 'Adyen "donate" transaction not constructing the expected redirect URL' );
		$this->assertNotNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Adyen order_id is null, and we need one for 'merchantReference'" );
	}

}
