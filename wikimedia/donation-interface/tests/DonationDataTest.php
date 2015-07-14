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
 * @group Splunge
 * @group DonationData
 */
class DonationInterface_DonationDataTest extends DonationInterfaceTestCase {

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	public function __construct( $name = null, array $data = array(), $dataName = '' ) {
		global $wgRequest;

		$adapterclass = TESTS_ADAPTER_DEFAULT;
		$this->testAdapterClass = $adapterclass;

		parent::__construct( $name, $data, $dataName );

		$this->testData = array(
			'amount' => '128.00',
			'email' => 'unittest@example.com',
			'fname' => 'Testocres',
			'lname' => 'McTestingyou',
			'street' => '123 Fake Street',
			'city' => 'Springfield',
			'state' => 'US',
			'zip' => '99999',
			'country' => 'US',
			'card_num' => '42',
			'card_type' => 'visa',
			'expiration' => '1138',
			'cvv' => '665',
			'currency_code' => 'USD',
			'payment_method' => 'cc',
			'payment_submethod' => 'visa',
			'numAttempt' => '5',
			'referrer' => 'http://www.testing.com/',
			'utm_source' => '..cc',
			'utm_medium' => 'large',
			'utm_campaign' => 'yes',
			'email-opt' => '',
			'test_string' => '',
			'token' => '113811',
			'contribution_tracking_id' => '',
			'data_hash' => '',
			'action' => '',
			'gateway' => 'DonationData',
			'owa_session' => '',
			'owa_ref' => 'http://localhost/importedTestData',
			'user_ip' => $wgRequest->getIP(),
			'server_ip' => $wgRequest->getIP(),
		);

	}


	/**
	 * @covers DonationData::__construct
	 * @covers DonationData::getDataEscaped
	 * @covers DonationData::populateData
	 */
	public function testConstruct(){
		global $wgLanguageCode, $wgRequest;

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ) ); //as if we were posted.
		$returned = $ddObj->getDataEscaped();
		$expected = array(  'posted' => '',
			'amount' => '0.00',
			'country' => 'XX',
			'payment_method' => '',
			'referrer' => '',
			'utm_source' => '..',
			'language' => $wgLanguageCode,
			'gateway' => 'globalcollect',
			'payment_submethod' => '',
			'recurring' => '',
			'user_ip' => $wgRequest->getIP(),
			'server_ip' => $wgRequest->getIP(),
		);
		unset($returned['contribution_tracking_id']);
		unset($returned['order_id']);
		$this->assertEquals($expected, $returned, "Staged post data does not match expected (largely empty).");
	}

	/**
	 * Test construction with external data (for tests and possible batch operations)
	 */
	public function testConstructWithExternalData() {
		global $wgRequest;

		$expected = array (
			'amount' => '35.00',
			'email' => 'testingdata@wikimedia.org',
			'fname' => 'Tester',
			'lname' => 'Testington',
			'street' => '548 Market St.',
			'city' => 'San Francisco',
			'state' => 'CA',
			'zip' => '94104',
			'country' => 'US',
			'card_num' => '378282246310005',
			'card_type' => 'amex',
			'expiration' => '0415',
			'cvv' => '001',
			'currency_code' => 'USD',
			'payment_method' => 'cc',
			'referrer' => 'http://www.baz.test.com/index.php?action=foo&amp;action=bar',
			'utm_source' => 'test_src..cc',
			'utm_medium' => 'test_medium',
			'utm_campaign' => 'test_campaign',
			'language' => 'en',
			'token' => '',
			'data_hash' => '',
			'action' => '',
			'gateway' => 'globalcollect',
			'owa_session' => '',
			'owa_ref' => 'http://localhost/defaultTestData',
			'street_supplemental' => '3rd floor',
			'payment_submethod' => 'amex',
			'issuer_id' => '',
			'utm_source_id' => '',
			'user_ip' => '12.12.12.12',
			'server_ip' => $wgRequest->getIP(),
			'recurring' => '',
		);

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $expected ); //external data
		$returned = $ddObj->getDataEscaped();


		$this->assertNotNull( $returned['contribution_tracking_id'], 'There is no contribution tracking ID' );
		$this->assertNotEquals( $returned['contribution_tracking_id'], '', 'There is not a valid contribution tracking ID' );

		unset($returned['order_id']);
		unset($returned['contribution_tracking_id']);

		$this->assertEquals($expected, $returned, "Staged default test data does not match expected.");
	}

	/**
	 * Test construction with data jammed in $_GET.
	 */
	public function testConstructWithFauxRequest() {
		global $wgRequest;

		$expected = array (
			'amount' => '35.00',
			'email' => 'testingdata@wikimedia.org',
			'fname' => 'Tester',
			'lname' => 'Testington',
			'street' => '548 Market St.',
			'city' => 'San Francisco',
			'state' => 'CA',
			'zip' => '94104',
			'country' => 'US',
			'card_num' => '378282246310005',
			'card_type' => 'amex',
			'expiration' => '0415',
			'cvv' => '001',
			'currency_code' => 'USD',
			'payment_method' => 'cc',
			'referrer' => 'http://www.baz.test.com/index.php?action=foo&amp;action=bar',
			'utm_source' => 'test_src..cc',
			'utm_medium' => 'test_medium',
			'utm_campaign' => 'test_campaign',
			'language' => 'en',
			'gateway' => 'globalcollect',
			'owa_ref' => 'http://localhost/getTestData',
			'street_supplemental' => '3rd floor',
			'payment_submethod' => 'amex',
			'user_ip' => $wgRequest->getIP(),
			'server_ip' => $wgRequest->getIP(),
			'recurring' => '',
			'posted' => '',
		);

		$this->setMwGlobals( 'wgRequest', new FauxRequest( $expected, false ) );

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ) ); //Get all data from $_GET
		$returned = $ddObj->getDataEscaped();

		$this->assertNotNull( $returned['contribution_tracking_id'], 'There is no contribution tracking ID' );
		$this->assertNotEquals( $returned['contribution_tracking_id'], '', 'There is not a valid contribution tracking ID' );

		unset( $returned['order_id'] );
		unset( $returned['contribution_tracking_id'] );

		$this->assertEquals( $expected, $returned, "Staged default test data does not match expected." );
	}

	/**
	 * Check that constructor outputs certain information to logs
	 */
	public function testDebugLog() {
		$expected = array (
			'payment_method' => 'cc',
			'utm_source' => 'test_src..cc',
			'utm_medium' => 'test_medium',
			'utm_campaign' => 'test_campaign',
			'payment_submethod' => 'amex',
			'currency_code' => 'USD',
		);

		$this->setMwGlobals( 'wgRequest', new FauxRequest( $expected, false ) );

		$ddObj = new DonationData( $this->getFreshGatewayObject( ) );
		$matches = $this->getLogMatches( LogLevel::DEBUG, '/setUtmSource: Payment method is cc, recurring = false, utm_source = cc$/' );
		$this->assertNotEmpty( $matches );
		$matches = $this->getLogMatches( LogLevel::DEBUG, "/Got currency from 'currency_code', now: USD$/" );
		$this->assertNotEmpty( $matches );
	}

	/**
	 *
	 */
	public function testRepopulate(){
		global $wgLanguageCode;

		$expected = $this->testData;

		// Some changes from the default
		$expected['recurring'] = '';
		$expected['language'] = $wgLanguageCode;
		$expected['gateway'] = 'globalcollect';

		// Just unset a handful... doesn't matter what, really.
		unset($expected['comment-option']);
		unset($expected['email-opt']);
		unset($expected['test_string']);

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $expected ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		//unset these, because they're always new
		$unsettable = array(
			'order_id',
			'contribution_tracking_id'
		);

		foreach ( $unsettable as $thing ) {
			unset( $returned[$thing] );
			unset( $expected[$thing] );
		}

		$this->assertEquals( $expected, $returned, "The forced test data did not populate as expected." );
	}

	/**
	 *
	 */
	public function testIsSomething(){
		$data = $this->testData;
		unset( $data['zip'] );

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$this->assertEquals($ddObj->isSomething('zip'), false, "Zip should currently be nothing.");
		$this->assertEquals($ddObj->isSomething('lname'), true, "Lname should currently be something.");
	}

	/**
	 *
	 */
	public function testSetNormalizedAmount_amtGiven() {
		$data = $this->testData;
		$data['amount'] = 'this is not a number';
		$data['amountGiven'] = 42.50;
		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals( 42.50, $returned['amount'], "Amount was not properly reset" );
		$this->assertArrayNotHasKey( 'amountGiven', $returned, "amountGiven should have been removed from the data" );
	}

	/**
	 *
	 */
	public function testSetNormalizedAmount_amount() {
		$data = $this->testData;
		$data['amount'] = 88.15;
		$data['amountGiven'] = 42.50;
		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals( 88.15, $returned['amount'], "Amount was not properly reset" );
		$this->assertArrayNotHasKey( 'amountGiven', $returned, "amountGiven should have been removed from the data" );
	}

	/**
	 *
	 */
	public function testSetNormalizedAmount_negativeAmount() {
		$data = $this->testData;
		$data['amount'] = -1;
		$data['amountOther'] = 3.25;
		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals(3.25, $returned['amount'], "Amount was not properly reset");
		$this->assertArrayNotHasKey( 'amountOther', $returned, "amountOther should have been removed from the data");
	}

	/**
	 *
	 */
	public function testSetNormalizedAmount_noGoodAmount() {
		$data = $this->testData;
		$data['amount'] = 'splunge';
		$data['amountGiven'] = 'wombat';
		$data['amountOther'] = 'macedonia';
		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals( 'invalid', $returned['amount'], "Amount was not properly reset");
		$this->assertArrayNotHasKey( 'amountOther', $returned, "amountOther should have been removed from the data");
		$this->assertArrayNotHasKey( 'amountGiven', $returned, "amountGiven should have been removed from the data");
	}

	/**
	 *
	 */
	public function testSetNormalizedLanguage_uselang() {
		$data = $this->testData;
		unset( $data['uselang'] );
		unset( $data['language'] );

		$data['uselang'] = 'no';

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals( 'no', $returned['language'], "Language 'no' was normalized out of existance. Sad." );
		$this->assertArrayNotHasKey( 'uselang', $returned, "'uselang' should have been removed from the data" );
	}

	/**
	 *
	 */
	public function testSetNormalizedLanguage_language() {
		$data = $this->testData;
		unset( $data['uselang'] );
		unset( $data['language'] );

		$data['language'] = 'no';

		$ddObj = new DonationData( $this->getFreshGatewayObject( self::$initial_vars ), $data ); //change to test mode with explicit test data
		$returned = $ddObj->getDataEscaped();
		$this->assertEquals( 'no', $returned['language'], "Language 'no' was normalized out of existance. Sad." );
		$this->assertArrayNotHasKey( 'uselang', $returned, "'uselang' should have been removed from the data" );
	}

	/**
	 * TODO: Make sure ALL these functions in DonationData are tested, either directly or through a calling function.
	 * I know that's more regression-ish, but I stand by it. :p
	function setNormalizedOrderIDs(){
	function generateOrderId() {
	public function sanitizeInput( &$value, $key, $flags=ENT_COMPAT, $double_encode=false ) {
	function setGateway(){
	function doCacheStuff(){
	public function getEditToken( $salt = '' ) {
	public static function generateToken( $salt = '' ) {
	function matchEditToken( $val, $salt = '' ) {
	function unsetEditToken() {
	public function checkTokens() {
	function wasPosted(){
	function setUtmSource() {
	public function getOptOuts() {
	public function getCleanTrackingData( $clean_optouts = false ) {
	function saveContributionTracking() {
	public static function insertContributionTracking( $tracking_data ) {
	public function updateContributionTracking( $force = false ) {

	*/
}


