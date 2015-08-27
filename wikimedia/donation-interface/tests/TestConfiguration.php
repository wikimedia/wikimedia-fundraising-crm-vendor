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
 * This file contains custom options and constants for test configuration.
 */

/**
 * TESTS_MESSAGE_NOT_IMPLEMENTED
 *
 * Message for code that has not been implemented.
 */
define( 'TESTS_MESSAGE_NOT_IMPLEMENTED', 'Not implemented yet!' );

/**
 * TESTS_HOSTNAME
 *
 * The hostname for the system
 */
define( 'TESTS_HOSTNAME', 'localhost' );

/**
 * TESTS_EMAIL
 *
 * An email address to use in case test send mail
 */
define( 'TESTS_EMAIL', 'nobody@wikimedia.org' );

/**
 * TESTS_GATEWAY_DEFAULT
 *
 * This is the default gateway that will be used to implement unit tests.
 */
define( 'TESTS_GATEWAY_DEFAULT', 'GlobalCollectGateway' );

/**
 * TESTS_ADAPTER_DEFAULT
 *
 * This is the default adapter that will be used to implement unit tests.
 */
define( 'TESTS_ADAPTER_DEFAULT', 'TestingGlobalCollectAdapter' );

global $wgDonationInterfaceTestMode,
	$wgDonationInterfaceMerchantID,
	$wgDonationInterfaceAllowedHtmlForms,
	$wgDonationInterfaceThankYouPage,
	$wgGlobalCollectGatewayAccountInfo,
	$wgPaypalGatewayAccountInfo,
	$wgPaypalGatewayReturnURL,
	$wgAmazonGatewayReturnURL,
	$wgAmazonGatewayAccountInfo,
	$wgAdyenGatewayBaseURL,
	$wgAdyenGatewayAccountInfo,
	$wgAstropayGatewayURL,
	$wgAstropayGatewayTestingURL,
	$wgAstropayGatewayAccountInfo,
	$wgWorldpayGatewayAccountInfo,
	$wgWorldpayGatewayURL,
	$wgMinFraudLicenseKey,
	$wgMinFraudTimeout,
	$wgDonationInterfaceMinFraudServers,
	$wgDonationInterfaceEnableMinfraud,
	$wgDonationInterfaceEnableFunctionsFilter,
	$wgDonationInterfaceEnableReferrerFilter,
	$wgDonationInterfaceEnableSourceFilter;

/**
 * Make sure the test setup is used, else we'll have the wrong classes.
 */
/** DonationInterface General Settings **/
$wgDonationInterfaceTestMode = true;
$wgDonationInterfaceMerchantID = 'test';

$wgDonationInterfaceAllowedHtmlForms = array(
	'test' => array(
	),
);

$wgDonationInterfaceThankYouPage = 'https://wikimediafoundation.org/wiki/Thank_You';


/** GlobalCollect **/
$wgGlobalCollectGatewayAccountInfo = array ( );
$wgGlobalCollectGatewayAccountInfo['test'] = array (
	'MerchantID' => 'test',
);


/** Paypal **/
$wgPaypalGatewayAccountInfo = array ( );
$wgPaypalGatewayAccountInfo['testing'] = array (
	'AccountEmail' => 'phpunittesting@wikimedia.org',
);
$wgPaypalGatewayReturnURL = 'http://donate.wikimedia.org'; //whatever, doesn't matter.


/** Amazon **/
$wgAmazonGatewayReturnURL = 'https://payments.wikimedia.org/index.php/Special:AmazonGateway';
$wgAmazonGatewayAccountInfo['test'] = array (
	'AccessKey' => 'testkey',
	'SecretKey' => 'testsecret',
	'PaymentsAccountID' => 'testaccountid',
	'IpnOverride' => 'https://test.wikimedia.org/amazon',
);

/** Adyen **/
$wgAdyenGatewayBaseURL = 'https://testorwhatever.adyen.com';
$wgAdyenGatewayAccountInfo['test'] = array (
	'AccountName' => 'wikitest',
	'SharedSecret' => 'long-cat-is-long',
	'SkinCode' => 'testskin',
);

/** Astropay **/
$wgAstropayGatewayURL = 'https://astropay.example.com/';
$wgAstropayGatewayTestingURL = 'https://sandbox.astropay.example.com/';
$wgAstropayGatewayAccountInfo['test'] = array (
	'Create' => array(
		'Login' => 'createlogin',
		'Password' => 'createpass',
	),
	'Status' => array(
		'Login' => 'statuslogin',
		'Password' => 'statuspass',
	),
	'SecretKey' => 'NanananananananananananananananaBatman',
);

/** Worldpay **/
$wgWorldpayGatewayAccountInfo['test'] = array (
	'Username' => 'testname',
	'Password' => 'testpass',
	'MerchantId' => '123456',
	'Test' => true,
	'TokenizingMerchantID' => '123456',
	'StoreIDs' => array (
		'*/FJ/EUR' => array( 123456, 'fj_store_id' ),
		'*/*/EUR' => array( 123456, 'eur_store_id' ),
		'*/*/USD' => array( 123456, 'usd_store_id' ),
	),
	'MerchantIDs' => array(
		123456 => array(
			'Username' => 'testname2',
			'Password' => 'testpass2',
		),
	),
	// Test special treatment - allow 'fail' CVV and missing AVS nodes
	'SpecialSnowflakeStoreIDs' => array(
		'fj_store_id',
	),
);
$wgWorldpayGatewayURL = 'https://test.worldpay.com';

$wgMinFraudLicenseKey = 'testkey';
$wgMinFraudTimeout = 1;
$wgDonationInterfaceMinFraudServers = array ( "minfraud.wikimedia.org" );

// Don't connect to the queue.
$wgDonationInterfaceEnableQueue = false;

//still can't quite handle mindfraud by itself yet, so default like this. 
//I will turn it on for individual tests in which I want to verify that it at
//least fails closed when enabled.
$wgDonationInterfaceEnableMinfraud = false;

//...but we want these. 
$wgDonationInterfaceEnableFunctionsFilter = true;
$wgDonationInterfaceEnableReferrerFilter = true;
$wgDonationInterfaceEnableSourceFilter = true;
