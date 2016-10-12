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
 *
 * @group Fundraising
 * @group DonationInterface
 * @group GlobalCollect
 */
class DonationInterface_Adapter_GlobalCollect_GlobalCollectTest extends DonationInterfaceTestCase {
	public function setUp() {
		parent::setUp();

		$this->setMwGlobals( array(
			'wgGlobalCollectGatewayEnabled' => true,
			'wgDonationInterfaceAllowedHtmlForms' => array(
				'cc-vmad' => array(
					'gateway' => 'globalcollect',
					'payment_methods' => array('cc' => array( 'visa', 'mc', 'amex', 'discover' )),
					'countries' => array(
						'+' => array( 'US', ),
					),
				),
			),
		) );
	}

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	function __construct( $name = null, array $data = array(), $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		$this->testAdapterClass = 'TestingGlobalCollectAdapter';
	}

	/**
	 * testnormalizeOrderID
	 * Non-exhaustive integration tests to verify that order_id
	 * normalization works as expected with different settings and
	 * conditions in theGlobalCollect adapter
	 * @covers GatewayAdapter::normalizeOrderID
	 */
	public function testNormalizeOrderID() {
		$request = $this->getDonorTestData();
		$externalData = $this->getDonorTestData();
		$session = array( 'Donor' => $this->getDonorTestData() );

		//no order_id from anywhere, explicit no generate
		$gateway = $this->getFreshGatewayObject( $externalData, array ( 'order_id_meta' => array ( 'generate' => FALSE ) ) );
		$this->assertFalse( $gateway->getOrderIDMeta( 'generate' ), 'The order_id meta generate setting override is not working properly. Deferred order_id generation may be broken.' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Failed asserting that an absent order id is left as null, when not generating our own' );

		//no order_id from anywhere, explicit generate
		$gateway = $this->getFreshGatewayObject( $externalData, array ( 'order_id_meta' => array ( 'generate' => TRUE ) ) );
		$this->assertTrue( $gateway->getOrderIDMeta( 'generate' ), 'The order_id meta generate setting override is not working properly. Self order_id generation may be broken.' );
		$this->assertInternalType( 'numeric', $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Generated order_id is not numeric, which it should be for GlobalCollect' );

		// conflicting order_id in request and session, default GC generation
		$request['order_id'] = '55555';
		$session['Donor']['order_id'] = '44444';
		$this->setUpRequest( $request, $session );
		$gateway = new TestingGlobalCollectAdapter();
		$this->assertEquals( '55555', $gateway->getData_Unstaged_Escaped( 'order_id' ), 'GlobalCollect gateway is preferring session data over the request. Session should be secondary.' );

		// conflicting order_id in request and session, garbage data in request, default GC generation
		$request['order_id'] = 'nonsense!';
		$this->setUpRequest( $request, $session );
		$gateway = new TestingGlobalCollectAdapter();
		$this->assertEquals( '44444', $gateway->getData_Unstaged_Escaped( 'order_id' ), 'GlobalCollect gateway is not ignoring nonsensical order_id candidates' );

		// order_id in session, default GC generation
		unset( $request['order_id'] );
		$this->setUpRequest( $request, $session );
		$gateway = new TestingGlobalCollectAdapter();
		$this->assertEquals( '44444', $gateway->getData_Unstaged_Escaped( 'order_id' ), 'GlobalCollect gateway is not recognizing the session order_id' );

		// conflicting order_id in external data, request and session, explicit GC generation, batch mode
		$request['order_id'] = '33333';
		$externalData['order_id'] = '22222';
		$this->setUpRequest( $request, $session );
		$gateway = $this->getFreshGatewayObject( $externalData, array ( 'order_id_meta' => array ( 'generate' => true ), 'batch_mode' => true ) );
		$this->assertEquals( $externalData['order_id'], $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Failed asserting that an extrenally provided order id is being honored in batch mode' );

		//make sure that decimal numbers are rejected by GC. Should be a toss and regen
		$externalData['order_id'] = '2143.0';
		unset( $request['order_id'] );
		unset( $session['Donor']['order_id'] );
		$this->setUpRequest( $request, $session );
		//conflicting order_id in external data, request and session, explicit GC generation, batch mode
		$gateway = $this->getFreshGatewayObject( $externalData, array ( 'order_id_meta' => array ( 'generate' => true, 'disallow_decimals' => true ), 'batch_mode' => true ) );
		$this->assertNotEquals( $externalData['order_id'], $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Failed assering that a decimal order_id was regenerated, when disallow_decimals is true' );
	}

	/**
	 * Non-exhaustive integration tests to verify that order_id, when in
	 * self-generation mode, won't regenerate until it is told to.
	 * @covers GatewayAdapter::normalizeOrderID
	 * @covers GatewayAdapter::regenerateOrderID
	 */
	function testStickyGeneratedOrderID() {
		$init = self::$initial_vars;
		unset( $init['order_id'] );

		//no order_id from anywhere, explicit generate
		$gateway = $this->getFreshGatewayObject( $init, array ( 'order_id_meta' => array ( 'generate' => TRUE ) ) );
		$this->assertNotNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Generated order_id is null. The rest of this test is broken.' );
		$original_order_id = $gateway->getData_Unstaged_Escaped( 'order_id' );

		$gateway->normalizeOrderID();
		$this->assertEquals( $original_order_id, $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Re-normalized order_id has changed without explicit regeneration.' );

		//this might look a bit strange, but we need to be able to generate valid order_ids without making them stick to anything.
		$gateway->generateOrderID();
		$this->assertEquals( $original_order_id, $gateway->getData_Unstaged_Escaped( 'order_id' ), 'function generateOrderID auto-changed the selected order ID. Not cool.' );

		$gateway->regenerateOrderID();
		$this->assertNotEquals( $original_order_id, $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Re-normalized order_id has not changed, after explicit regeneration.' );
	}

	/**
	 * Integration test to verify that order_id can be retrieved from
	 * performing an INSERT_ORDERWITHPAYMENT.
	 */
	function testOrderIDRetrieval() {
		$init = $this->getDonorTestData();
		unset( $init['order_id'] );
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';

		//no order_id from anywhere, explicit generate
		$gateway = $this->getFreshGatewayObject( $init, array ( 'order_id_meta' => array ( 'generate' => FALSE ) ) );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), 'Ungenerated order_id is not null. The rest of this test is broken.' );

		$gateway->do_transaction( 'INSERT_ORDERWITHPAYMENT' );

		$this->assertNotNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), 'No order_id was retrieved from INSERT_ORDERWITHPAYMENT' );
	}

	/**
	 * Just run the GET_ORDERSTATUS transaction and make sure we load the data
	 */
	function testGetOrderStatus() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@safedomain.org';

		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->do_transaction( 'GET_ORDERSTATUS' );

		$data = $gateway->getTransactionData();

		$this->assertEquals( 'N', $data['CVVRESULT'], 'CVV Result not loaded from XML response' );
	}

	/**
	 * Don't fraud-fail someone for bad CVV if GET_ORDERSTATUS
	 * comes back with STATUSID 25 and no CVVRESULT
	 * @group CvvResult
	 */
	function testConfirmCreditCardStatus25() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@safedomain.org';

		$this->setUpRequest( array( 'CVVRESULT' => 'M' ) );

		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( '25' );

		$gateway->do_transaction( 'Confirm_CreditCard' );
		$action = $gateway->getValidationAction();
		$this->assertEquals( 'process', $action, 'Gateway should not fraud fail on STATUSID 25' );
	}

	/**
	 * Make sure we're incorporating GET_ORDERSTATUS AVS and CVV responses into
	 * fraud scores.
	 */
	function testGetOrderstatusPostProcessFraud() {
		$this->setMwGlobals( array(
			'wgDonationInterfaceEnableCustomFilters' => true,
			'wgGlobalCollectGatewayCustomFiltersFunctions' => array(
				'getCVVResult' => 10,
				'getAVSResult' => 30,
			),
		) );

		$init = $this->getDonorTestData();
		$init['ffname'] = 'cc-vmad';
		$init['order_id'] = '55555';
		$init['email'] = 'innocent@manichean.com';
		$init['contribution_tracking_id'] = mt_rand();
		$init['payment_method'] = 'cc';
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( '600_badCvv' );

		$gateway->do_transaction( 'Confirm_CreditCard' );
		$action = $gateway->getValidationAction();
		$this->assertEquals( 'review', $action,
			'Orphan gateway should fraud fail on bad CVV and AVS' );

		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$this->assertEquals( 40, $exposed->risk_score,
			'Risk score was incremented correctly.' );
	}

	/**
	 * Ensure the Confirm_CreditCard transaction prefers CVVRESULT from the XML
	 * over any value from the querystring
	 */
	function testConfirmCreditCardPrefersXmlCvv() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@safedomain.org';

		$this->setUpRequest( array( 'CVVRESULT' => 'M' ) );

		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->do_transaction( 'Confirm_CreditCard' );

		$this->assertEquals( 'N', $gateway->getData_Unstaged_Escaped('cvv_result'), 'CVV Result not taken from XML response' );
	}

	/**
	 * Make sure we record the actual amount charged, even if the donor has
	 * opened a new window and screwed up their session data.
	 */
	function testConfirmCreditCardUpdatesAmount() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@safedomain.org';
		// The values in session are not the values we originally used
		// for INSERT_ORDERWITHPAYMENT
		$init['amount'] = '12.50';
		$init['currency_code'] = 'USD';

		$gateway = $this->getFreshGatewayObject( $init );

		$amount = $gateway->getData_Unstaged_Escaped( 'amount' );
		$currency = $gateway->getData_Unstaged_Escaped( 'currency_code' );
		$this->assertEquals( '12.50', $amount );
		$this->assertEquals( 'USD', $currency );

		$gateway->do_transaction( 'Confirm_CreditCard' );

		$amount = $gateway->getData_Unstaged_Escaped( 'amount' );
		$currency = $gateway->getData_Unstaged_Escaped( 'currency_code' );
		$this->assertEquals( '23.45', $amount, 'Not recording correct amount' );
		$this->assertEquals( 'EUR', $currency, 'Not recording correct currency'  );
	}

	/**
	 * testDefineVarMap
	 *
	 * This is tested with a bank transfer from Spain.
	 *
	 * @covers GlobalCollectAdapter::__construct
	 * @covers GlobalCollectAdapter::defineVarMap
	 */
	public function testDefineVarMap() {

		$gateway = $this->getFreshGatewayObject( self::$initial_vars );

		$var_map = array(
			'ORDERID' => 'order_id',
			'AMOUNT' => 'amount',
			'CURRENCYCODE' => 'currency_code',
			'LANGUAGECODE' => 'language',
			'COUNTRYCODE' => 'country',
			'MERCHANTREFERENCE' => 'contribution_tracking_id',
			'RETURNURL' => 'returnto',
			'IPADDRESS' => 'server_ip',
			'ISSUERID' => 'issuer_id',
			'PAYMENTPRODUCTID' => 'payment_product',
			'CVV' => 'cvv',
			'EXPIRYDATE' => 'expiration',
			'CREDITCARDNUMBER' => 'card_num',
			'FIRSTNAME' => 'fname',
			'SURNAME' => 'lname',
			'STREET' => 'street',
			'CITY' => 'city',
			'STATE' => 'state',
			'ZIP' => 'zip',
			'EMAIL' => 'email',
			'ACCOUNTHOLDER' => 'account_holder',
			'ACCOUNTNAME' => 'account_name',
			'ACCOUNTNUMBER' => 'account_number',
			'ADDRESSLINE1E' => 'address_line_1e',
			'ADDRESSLINE2' => 'address_line_2',
			'ADDRESSLINE3' => 'address_line_3',
			'ADDRESSLINE4' => 'address_line_4',
			'ATTEMPTID' => 'attempt_id',
			'AUTHORISATIONID' => 'authorization_id',
			'BANKACCOUNTNUMBER' => 'bank_account_number',
			'BANKAGENZIA' => 'bank_agenzia',
			'BANKCHECKDIGIT' => 'bank_check_digit',
			'BANKCODE' => 'bank_code',
			'BANKFILIALE' => 'bank_filiale',
			'BANKNAME' => 'bank_name',
			'BRANCHCODE' => 'branch_code',
			'COUNTRYCODEBANK' => 'country_code_bank',
			'COUNTRYDESCRIPTION' => 'country_description',
			'CUSTOMERBANKCITY' => 'customer_bank_city',
			'CUSTOMERBANKSTREET' => 'customer_bank_street',
			'CUSTOMERBANKNUMBER' => 'customer_bank_number',
			'CUSTOMERBANKZIP' => 'customer_bank_zip',
			'DATECOLLECT' => 'date_collect',
			'DESCRIPTOR' => 'descriptor',
			'DIRECTDEBITTEXT' => 'direct_debit_text',
			'DOMICILIO' => 'domicilio',
			'EFFORTID' => 'effort_id',
			'IBAN' => 'iban',
			'IPADDRESSCUSTOMER' => 'user_ip',
			'PAYMENTREFERENCE' => 'payment_reference',
			'PROVINCIA' => 'provincia',
			'SPECIALID' => 'special_id',
			'SWIFTCODE' => 'swift_code',
			'TRANSACTIONTYPE' => 'transaction_type',
			'FISCALNUMBER' => 'fiscal_number',
			'AVSRESULT' => 'avs_result',
			'CVVRESULT' => 'cvv_result',
		);

		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$this->assertEquals( $var_map, $exposed->var_map );
	}

	public function testLanguageStaging() {
		$options = $this->getDonorTestData( 'NO' );
		$options['payment_method'] = 'cc';
		$options['payment_submethod'] = 'visa';
		$gateway = $this->getFreshGatewayObject( $options );

		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$exposed->stageData();

		$this->assertEquals( $exposed->getData_Staged( 'language' ), 'no', "'NO' donor's language was inproperly set. Should be 'no'" );
	}

	public function testLanguageFallbackStaging() {
		$options = $this->getDonorTestData( 'Catalonia' );
		$options['payment_method'] = 'cc';
		$options['payment_submethod'] = 'visa';
		$gateway = $this->getFreshGatewayObject( $options );

		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$exposed->stageData();

		// Requesting the fallback language from the gateway.
		$this->assertEquals( 'en', $exposed->getData_Staged( 'language' ) );
	}

	/**
	 * Make sure unstaging functions don't overwrite core donor data.
	 */
	public function testAddResponseData_underzealous() {
		$options = $this->getDonorTestData( 'Catalonia' );
		$options['payment_method'] = 'cc';
		$options['payment_submethod'] = 'visa';
		$gateway = $this->getFreshGatewayObject( $options );

		// This will set staged_data['language'] = 'en'.
		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$exposed->stageData();

		$ctid = mt_rand();

		$gateway->addResponseData( array(
			'contribution_tracking_id' => $ctid . '.1',
		) );

		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		// Desired vars were written into normalized data.
		$this->assertEquals( $ctid, $exposed->dataObj->getVal_Escaped( 'contribution_tracking_id' ) );

		// Language was not overwritten.
		$this->assertEquals( 'ca', $exposed->dataObj->getVal_Escaped( 'language' ) );
	}

	/**
	 * Tests to make sure that certain error codes returned from GC will or
	 * will not create payments error loglines.
	 */
	function testCCLogsOnGatewayError() {
		$init = $this->getDonorTestData( 'US' );
		unset( $init['order_id'] );
		$init['ffname'] = 'cc-vmad';

		//this should not throw any payments errors: Just an invalid card.
		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( '430285' );
		$gateway->do_transaction( 'GET_ORDERSTATUS' );
		$this->verifyNoLogErrors();

		//Now test one we want to throw a payments error
		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( '21000050' );
		$gateway->do_transaction( 'GET_ORDERSTATUS' );
		$loglines = $this->getLogMatches( LogLevel::ERROR, '/Investigation required!/' );
		$this->assertNotEmpty( $loglines, 'GC Error 21000050 is not generating the expected payments log error' );

		//Reset logs
		$this->testLogger->messages = array();

		//Most irritating version of 20001000 - They failed to enter an expiration date on GC's form. This should log some specific info, but not an error.
		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( '20001000-expiry' );
		$gateway->do_transaction( 'GET_ORDERSTATUS' );
		$this->verifyNoLogErrors();
		$loglines = $this->getLogMatches( LogLevel::INFO, '/processResponse:.*EXPIRYDATE/' );
		$this->assertNotEmpty( $loglines, 'GC Error 20001000-expiry is not generating the expected payments log line' );
	}

	/**
	 * Tests to make sure that certain error codes returned from GC will
	 * trigger order cancellation, even if retryable errors also exist.
	 * @dataProvider mcNoRetryCodeProvider
	 */
	public function testNoMastercardFinesForRepeatOnBadCodes( $code ) {
		$init = $this->getDonorTestData( 'US' );
		unset( $init['order_id'] );
		$init['ffname'] = 'cc-vmad';
		//Make it not look like an orphan
		$this->setUpRequest( array(
			'CVVRESULT' => 'M',
			'AVSRESULT' => '0'
		) );

		//Toxic card should not retry, even if there's an order id collision
		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( $code );
		$gateway->do_transaction( 'Confirm_CreditCard' );
		$this->assertEquals( 1, count( $gateway->curled ), "Gateway kept trying even with response code $code!  MasterCard could fine us a thousand bucks for that!" );
	}

	/**
	 * Tests that two API requests don't send the same order ID and merchant
	 * reference.  This was the case when users doubleclicked and we were
	 * using the last 5 digits of time in seconds as a suffix.  We want to see
	 * what happens when a 2nd request comes in while the 1st is still waiting
	 * for a CURL response, so here we fake that situation by having CURL throw
	 * an exception during the 1st response.
	 */
	public function testNoDupeOrderId( ) {
		$this->setUpRequest( array(
			'action'=>'donate',
			'amount'=>'3.00',
			'card_type'=>'amex',
			'city'=>'Hollywood',
			'contribution_tracking_id'=>'22901382',
			'country'=>'US',
			'currency_code'=>'USD',
			'email'=>'FaketyFake@gmail.com',
			'fname'=>'Fakety',
			'format'=>'json',
			'gateway'=>'globalcollect',
			'language'=>'en',
			'lname'=>'Fake',
			'payment_method'=>'cc',
			'referrer'=>'http://en.wikipedia.org/wiki/Main_Page',
			'state'=>'MA',
			'street'=>'99 Fake St',
			'utm_campaign'=>'C14_en5C_dec_dsk_FR',
			'utm_medium'=>'sitenotice',
			'utm_source'=>'B14_120921_5C_lg_fnt_sans.no-LP.cc',
			'zip'=>'90210'
		) );

		$gateway = new TestingGlobalCollectAdapter( array( 'api_request' => 'true' ) );
		$gateway->setDummyGatewayResponseCode( 'Exception' );
		try {
			$gateway->do_transaction( 'INSERT_ORDERWITHPAYMENT' );
		}
		catch ( Exception $e ) {
			// totally expected this
		}
		$first = $gateway->curled[0];
		//simulate another request coming in before we get anything back from GC
		$anotherGateway = new TestingGlobalCollectAdapter( array( 'api_request' => 'true' ) );
		$anotherGateway->do_transaction( 'INSERT_ORDERWITHPAYMENT' );
		$second = $anotherGateway->curled[0];
		$this->assertFalse( $first == $second, 'Two calls to the api did the same thing');
	}

	/**
	 * Tests to see that we don't claim we're going to retry when we aren't
	 * going to. For GC, we really only want to retry on code 300620
	 * @dataProvider benignNoRetryCodeProvider
	 */
	public function testNoClaimRetryOnBoringCodes( $code ) {
		$init = $this->getDonorTestData( 'US' );
		unset( $init['order_id'] );
		$init['ffname'] = 'cc-vmad';
		//Make it not look like an orphan
		$this->setUpRequest( array(
			'CVVRESULT' => 'M',
			'AVSRESULT' => '0'
		) );

		$gateway = $this->getFreshGatewayObject( $init );
		$gateway->setDummyGatewayResponseCode( $code );
		$exposed = TestingAccessWrapper::newFromObject( $gateway );
		$start_id = $exposed->getData_Staged( 'order_id' );
		$gateway->do_transaction( 'Confirm_CreditCard' );
		$finish_id = $exposed->getData_Staged( 'order_id' );
		$loglines = $this->getLogMatches( LogLevel::INFO, '/Repeating transaction on request for vars:/' );
		$this->assertEmpty( $loglines, "Log says we are going to repeat the transaction for code $code, but that is not true" );
		$this->assertEquals( $start_id, $finish_id, "Needlessly regenerated order id for code $code ");
	}

	/**
	 * doPayment should return an iframe result with normal data
	 */
	function testDoPaymentSuccess() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@clean.com';
		$init['ffname'] = 'cc-vmad';
		unset( $init['order_id'] );

		$gateway = $this->getFreshGatewayObject( $init );
		$result = $gateway->doPayment();
		$this->assertEmpty( $result->isFailed(), 'PaymentResult should not be failed' );
		$this->assertEmpty( $result->getErrors(), 'PaymentResult should have no errors' );
		$this->assertEquals( 'url_placeholder', $result->getIframe(), 'PaymentResult should have iframe set' );
	}

	/**
	 * doPayment should recover from an attempt to use a duplicate order ID.
	 */
	function testDuplicateOrderId() {
		$init = $this->getDonorTestData();
		$init['payment_method'] = 'cc';
		$init['payment_submethod'] = 'visa';
		$init['email'] = 'innocent@localhost.net';
		$init['ffname'] = 'cc-vmad';
		unset( $init['order_id'] );

		$gateway = $this->getFreshGatewayObject( $init );
		$orig_id = $gateway->getData_Unstaged_Escaped( 'order_id' );
		$gateway->setDummyGatewayResponseCode( function ( $gateway ) use ( $orig_id ) {
			if ( $gateway->getData_Unstaged_Escaped( 'order_id' ) === $orig_id ) {
				return 'duplicate';
			} else {
				return null;
			}
		} );
		$result = $gateway->doPayment();
		$this->assertEmpty( $result->isFailed(), 'PaymentResult should not be failed' );
		$this->assertEmpty( $result->getErrors(), 'PaymentResult should have no errors' );
		$this->assertNotEquals( $gateway->getData_Unstaged_Escaped( 'order_id' ), $orig_id,
			'Order ID regenerated in DonationData.' );
		$this->assertNotEquals( $gateway->session_getData( 'order_id' ), $orig_id,
			'Order ID regenerated in session.' );
	}
}
