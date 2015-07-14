<?php

/**
 * Donation Interface
 *
 *  To install the DontaionInterface extension, put the following line in LocalSettings.php:
 *	require_once( "\$IP/extensions/DonationInterface/DonationInterface.php" );
 *
 */


# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install the DonationInterface extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/DonationInterface/DonationInterface.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Donation Interface',
	'author' => array( 'Elliott Eggleston', 'Katie Horn', 'Ryan Kaldari' , 'Arthur Richards', 'Sherah Smith', 'Matt Walker', 'Adam Wight', 'Peter Gehres', 'Jeremy Postlethwaite' ),
	'version' => '2.1.0',
	'descriptionmsg' => 'donationinterface-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:DonationInterface',
);

// Test mode (not for production!)
// Set it if not defined
if ( !isset( $wgDonationInterfaceTestMode) || $wgDonationInterfaceTestMode !== true ) {
	$wgDonationInterfaceTestMode = false;
}


/**
 * CLASSES
 */
$wgAutoloadClasses['CurrencyRates'] = __DIR__ . '/gateway_common/CurrencyRates.php';
$wgAutoloadClasses['DonationData'] = __DIR__ . '/gateway_common/DonationData.php';
$wgAutoloadClasses['DonationLoggerFactory'] = __DIR__ . '/gateway_common/DonationLoggerFactory.php';
$wgAutoloadClasses['DonationLogProcessor'] = __DIR__ . '/gateway_common/DonationLogProcessor.php';
$wgAutoloadClasses['DonationQueue'] = __DIR__ . '/gateway_common/DonationQueue.php';
$wgAutoloadClasses['EncodingMangler'] = __DIR__ . '/gateway_common/EncodingMangler.php';
$wgAutoloadClasses['FinalStatus'] = __DIR__ . '/gateway_common/FinalStatus.php';
$wgAutoloadClasses['GatewayAdapter'] = __DIR__ . '/gateway_common/gateway.adapter.php';
$wgAutoloadClasses['GatewayPage'] = __DIR__ . '/gateway_common/GatewayPage.php';
$wgAutoloadClasses['GatewayType'] = __DIR__ . '/gateway_common/gateway.adapter.php';
$wgAutoloadClasses['DataValidator'] = __DIR__ . '/gateway_common/DataValidator.php';
$wgAutoloadClasses['LogPrefixProvider'] = __DIR__ . '/gateway_common/gateway.adapter.php';
$wgAutoloadClasses['MessageUtils'] = __DIR__ . '/gateway_common/MessageUtils.php';
$wgAutoloadClasses['NationalCurrencies'] = __DIR__ . '/gateway_common/NationalCurrencies.php';
$wgAutoloadClasses['PaymentMethod'] = __DIR__ . '/gateway_common/PaymentMethod.php';
$wgAutoloadClasses['PaymentResult'] = __DIR__ . '/gateway_common/PaymentResult.php';
$wgAutoloadClasses['PaymentTransactionResponse'] = __DIR__ . '/gateway_common/PaymentTransactionResponse.php';
$wgAutoloadClasses['ResponseCodes'] = __DIR__ . '/gateway_common/ResponseCodes.php';
$wgAutoloadClasses['ResponseProcessingException'] = __DIR__ . '/gateway_common/ResponseProcessingException.php';
$wgAutoloadClasses['WmfFramework_Mediawiki'] = __DIR__ . '/gateway_common/WmfFramework.mediawiki.php';
$wgAutoloadClasses['WmfFrameworkLogHandler'] = __DIR__ . '/gateway_common/WmfFrameworkLogHandler.php';

//load all possible form classes
$wgAutoloadClasses['Gateway_Form'] = __DIR__ . '/gateway_forms/Form.php';
$wgAutoloadClasses['Gateway_Form_Mustache'] = __DIR__ . '/gateway_forms/Mustache.php';
$wgAutoloadClasses['Gateway_Form_RapidHtml'] = __DIR__ . '/gateway_forms/RapidHtml.php';
$wgAutoloadClasses['CountryCodes'] = __DIR__ . '/gateway_forms/includes/CountryCodes.php';
$wgAutoloadClasses['ProvinceAbbreviations'] = __DIR__ . '/gateway_forms/includes/ProvinceAbbreviations.php';
$wgAutoloadClasses['StateAbbreviations'] = __DIR__ . '/gateway_forms/includes/StateAbbreviations.php';

//GlobalCollect gateway classes
$wgAutoloadClasses['GlobalCollectGateway'] = __DIR__ . '/globalcollect_gateway/globalcollect_gateway.body.php';
$wgAutoloadClasses['GlobalCollectGatewayResult'] = __DIR__ . '/globalcollect_gateway/globalcollect_resultswitcher.body.php';

$wgAutoloadClasses['GlobalCollectAdapter'] = __DIR__ . '/globalcollect_gateway/globalcollect.adapter.php';
$wgAutoloadClasses['GlobalCollectOrphanAdapter'] = __DIR__ . '/globalcollect_gateway/scripts/orphan_adapter.php';
$wgAutoloadClasses['GlobalCollectOrphanRectifier'] = __DIR__ . '/globalcollect_gateway/scripts/orphans.php';

// Amazon
$wgAutoloadClasses['AmazonGateway'] = __DIR__ . '/amazon_gateway/amazon_gateway.body.php';
$wgAutoloadClasses['AmazonAdapter'] = __DIR__ . '/amazon_gateway/amazon.adapter.php';

//Adyen
$wgAutoloadClasses['AdyenGateway'] = __DIR__ . '/adyen_gateway/adyen_gateway.body.php';
$wgAutoloadClasses['AdyenGatewayResult'] = __DIR__ . '/adyen_gateway/adyen_resultswitcher.body.php';
$wgAutoloadClasses['AdyenAdapter'] = __DIR__ . '/adyen_gateway/adyen.adapter.php';

// Astropay
$wgAutoloadClasses['AstropayGateway'] = __DIR__ . '/astropay_gateway/astropay_gateway.body.php';
$wgAutoloadClasses['AstropayGatewayResult'] = __DIR__ . '/astropay_gateway/astropay_resultswitcher.body.php';
$wgAutoloadClasses['AstropayAdapter'] = __DIR__ . '/astropay_gateway/astropay.adapter.php';

// Paypal
$wgAutoloadClasses['PaypalGateway'] = __DIR__ . '/paypal_gateway/paypal_gateway.body.php';
$wgAutoloadClasses['PaypalGatewayResult'] = __DIR__ . '/paypal_gateway/paypal_resultswitcher.body.php';
$wgAutoloadClasses['PaypalAdapter'] = __DIR__ . '/paypal_gateway/paypal.adapter.php';

// Worldpay
$wgAutoloadClasses['WorldpayGateway'] = __DIR__ . '/worldpay_gateway/worldpay_gateway.body.php';
$wgAutoloadClasses['WorldpayAdapter'] = __DIR__ . '/worldpay_gateway/worldpay.adapter.php';

$wgAPIModules['di_wp_validate'] = 'WorldpayValidateApi';
$wgAutoloadClasses['WorldpayValidateApi'] = __DIR__ . '/worldpay_gateway/worldpay.api.php';

//Extras classes - required for ANY optional class that is considered an "extra".
$wgAutoloadClasses['Gateway_Extras'] = __DIR__ . '/extras/extras.body.php';

//Custom Filters classes
$wgAutoloadClasses['Gateway_Extras_CustomFilters'] = __DIR__ . '/extras/custom_filters/custom_filters.body.php';

//Conversion Log classes
$wgAutoloadClasses['Gateway_Extras_ConversionLog'] = __DIR__ . '/extras/conversion_log/conversion_log.body.php';

$wgAutoloadClasses['Gateway_Extras_CustomFilters_MinFraud'] = __DIR__ . '/extras/custom_filters/filters/minfraud/minfraud.body.php';
$wgAutoloadClasses['Gateway_Extras_CustomFilters_Referrer'] = __DIR__ . '/extras/custom_filters/filters/referrer/referrer.body.php';
$wgAutoloadClasses['Gateway_Extras_CustomFilters_Source'] = __DIR__ . '/extras/custom_filters/filters/source/source.body.php';
$wgAutoloadClasses['Gateway_Extras_CustomFilters_Functions'] = __DIR__ . '/extras/custom_filters/filters/functions/functions.body.php';
$wgAutoloadClasses['Gateway_Extras_CustomFilters_IP_Velocity'] = __DIR__ . '/extras/custom_filters/filters/ip_velocity/ip_velocity.body.php';

$wgAutoloadClasses['Gateway_Extras_SessionVelocityFilter'] = __DIR__ . '/extras/session_velocity/session_velocity.body.php';
$wgAutoloadClasses['GatewayFormChooser'] = __DIR__ . '/special/GatewayFormChooser.php';
$wgAutoloadClasses['SystemStatus'] = __DIR__ . '/special/SystemStatus.php';

/**
 * GLOBALS
 */

/**
 * Global form dir
 */
$wgDonationInterfaceHtmlFormDir = __DIR__ . '/gateway_forms/rapidhtml/html';
$wgDonationInterfaceTest = false;

/**
 * Default top-level template file.
 */
$wgDonationInterfaceTemplate = __DIR__ . '/gateway_forms/mustache/index.html.mustache';

/**
 * Title to transclude in form template as {{{ appeal_text }}}.
 * $appeal and $language will be substituted before transclusion
 */
$wgDonationInterfaceAppealWikiTemplate = 'LanguageSwitch|2011FR/$appeal/text|$language';

// Email address used when donor enters nothing
$wgDonationInterfaceDefaultEmail = 'nobody@wikimedia.org';

//all of the following variables make sense to override directly,
//or change "DonationInterface" to the gateway's id to override just for that gateway.
//for instance: To override $wgDonationInterfaceUseSyslog just for GlobalCollect, add
// $wgGlobalCollectGatewayUseSyslog = true
// to LocalSettings.
//

$wgDonationInterfaceDisplayDebug = false;
$wgDonationInterfaceUseSyslog = false;
$wgDonationInterfaceSaveCommStats = false;

$wgDonationInterfaceCSSVersion = 1;
$wgDonationInterfaceTimeout = 5;
$wgDonationInterfaceDefaultForm = 'RapidHtml';

/**
 * If set to a currency code, gateway forms will try to convert amounts
 * in unsupported currencies to the fallback instead of just showing
 * an unsupported currency error.
 */
$wgDonationInterfaceFallbackCurrency = false;

/**
 * When this is true and an unsupported currency has been converted to the
 * fallback (see above), we show an interstitial page notifying the user
 * of the conversion before sending the donation to the gateway.
 */
$wgDonationInterfaceNotifyOnConvert = true;

/**
 * A string or array of strings for making tokens more secure
 *
 * Please set this!  If you do not, tokens are easy to get around, which can
 * potentially leave you and your users vulnerable to CSRF or other forms of
 * attack.
 */
$wgDonationInterfaceSalt = $wgSecretKey;

/**
 * A string that can contain wikitext to display at the head of the credit card form
 *
 * This string gets run like so: $wg->addHtml( $wg->Parse( $wgGlobalCollectGatewayHeader ))
 * You can use '@language' as a placeholder token to extract the user's language.
 *
 */
$wgDonationInterfaceHeader = NULL;

/**
 * A string containing full URL for Javascript-disabled credit card form redirect
 */
$wgDonationInterfaceNoScriptRedirect = null;

/**
 * Configure price ceiling and floor for valid contribution amount.  Values
 * should be in USD.
 */
$wgDonationInterfacePriceFloor = 1.00;
$wgDonationInterfacePriceCeiling = 10000.00;

/**
 * Default Thank You and Fail pages for all of donationinterface - language will be calc'd and appended at runtime.
 */
//$wgDonationInterfaceThankYouPage = 'https://wikimediafoundation.org/wiki/Thank_You';
$wgDonationInterfaceThankYouPage = 'Donate-thanks';
$wgDonationInterfaceFailPage = 'Donate-error'; 

/**
 * Retry Loop Count - If there's a place where the API can choose to loop on some retry behavior, do it this number of times. 
 */
$wgDonationInterfaceRetryLoopCount = 3;

/**
 * Orphan Cron settings global
 */
$wgDonationInterfaceOrphanCron = array(
	'enable' => true,
	'target_execute_time' => 300,
	'max_per_execute' => '',
);

/**
 * Forbidden countries. No donations will be allowed to come in from countries 
 * in this list.
 * All should be represented as all-caps ISO 3166-1 alpha-2
 * This one global shouldn't ever be overridden per gateway. As it's probably 
 * going to only conatin countries forbidden by law, there's no reason
 * to override by gateway and as such it's always referenced directly. 
 */
$wgDonationInterfaceForbiddenCountries = array();

/**
 * 3D Secure enabled currencies (and countries) for Credit Card.
 * An array in the form of currency => array of countries 
 * (all-caps ISO 3166-1 alpha-2), or an empty array for all transactions in that
 * currency regardless of country of origin.
 * As this is a mandatroy check for all INR transactions, that rule made it into
 * the default.  
 */
$wgDonationInterface3DSRules = array(
	'INR' => array(), //all countries
);

//GlobalCollect gateway globals
$wgGlobalCollectGatewayURL = 'https://ps.gcsip.nl/wdl/wdl';
$wgGlobalCollectGatewayTestingURL = 'https://'; // GlobalCollect testing URL

#	$wgGlobalCollectGatewayAccountInfo['example'] = array(
#		'MerchantID' => '', // GlobalCollect ID
#	);

$wgGlobalCollectGatewayHtmlFormDir = __DIR__ . '/globalcollect_gateway/forms/html';

$wgGlobalCollectGatewayCvvMap = array(
	'M' => true, //CVV check performed and valid value.
	'N' => false, //CVV checked and no match.
	'P' => true, //CVV check not performed, not requested
	'S' => false, //Card holder claims no CVV-code on card, issuer states CVV-code should be on card. 
	'U' => true, //? //Issuer not certified for CVV2.
	'Y' => false, //Server provider did not respond.
	'0' => true, //No service available.
	'' => false, //No code returned. All the points.
);

$wgGlobalCollectGatewayAvsMap = array(
	'A' => 50, //Address (Street) matches, Zip does not.
	'B' => 50, //Street address match for international transactions. Postal code not verified due to incompatible formats.
	'C' => 50, //Street address and postal code not verified for international transaction due to incompatible formats.
	'D' => 0, //Street address and postal codes match for international transaction.
	'E' => 100, //AVS Error.
	'F' => 0, //Address does match and five digit ZIP code does match (UK only).
	'G' => 50, //Address information is unavailable; international transaction; non-AVS participant. 
	'I' => 50, //Address information not verified for international transaction.
	'M' => 0, //Street address and postal codes match for international transaction.
	'N' => 100, //No Match on Address (Street) or Zip.
	'P' => 50, //Postal codes match for international transaction. Street address not verified due to incompatible formats.
	'R' => 100, //Retry, System unavailable or Timed out.
	'S' => 50, //Service not supported by issuer.
	'U' => 50, //Address information is unavailable.
	'W' => 50, //9 digit Zip matches, Address (Street) does not.
	'X' => 0, //Exact AVS Match.
	'Y' => 0, //Address (Street) and 5 digit Zip match.
	'Z' => 50, //5 digit Zip matches, Address (Street) does not.
	'0' => 25, //No service available.
	'' => 100, //No code returned. All the points.
);	


//n.b. "-Testing-" urls are not wired to anything, they're just here for
// your copy n paste pleasure.

$wgAmazonGatewayURL = "https://authorize.payments.amazon.com/pba/paypipeline";
$wgAmazonGatewayTestingURL = "https://authorize.payments-sandbox.amazon.com/pba/paypipeline";

$wgAmazonGatewayFpsURL = "https://fps.amazonaws.com/";
$wgAmazonGatewayFpsTestingURL = "https://fps.sandbox.amazonaws.com/";

#	$wgAmazonGatewayAccountInfo['example'] = array(
#		'AccessKey' => "",
#		'SecretKey' => "",
#
#		// the long one, not the AWS account ID
#		'PaymentsAccountID' => "",
#	);

// e.g. http://payments.wikimedia.org/index.php/Special:AmazonGateway  --
// does NOT accept unroutable development names, use the number instead
// even if it's 127.0.0.1
$wgAmazonGatewayReturnURL = "";

$wgAmazonGatewayHtmlFormDir = __DIR__ . '/amazon_gateway/forms/html';

$wgPaypalGatewayURL = 'https://www.paypal.com/cgi-bin/webscr';
$wgPaypalGatewayTestingURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
$wgPaypalGatewayReturnURL = ''; //'http://127.0.0.1/index.php/Special:PaypalGatewayResult';
$wgPaypalGatewayRecurringLength = '0'; // 0 should mean forever

$wgPaypalGatewayHtmlFormDir = __DIR__ . '/paypal_gateway/forms/html';

$wgPaypalGatewayXclickCountries = array();

#	$wgPaypalGatewayAccountInfo['example'] = array(
#		'AccountEmail' => "",
#	);

$wgAdyenGatewayHtmlFormDir = __DIR__ . '/adyen_gateway/forms/html';

$wgAdyenGatewayBaseURL = 'https://live.adyen.com';
$wgAdyenGatewayBaseTestingURL = 'https://test.adyen.com'; // unused

#	$wgAdyenGatewayAccountInfo['example'] = array(
#		'AccountName' => ''; // account identifier, not login name
#		'SharedSecret' => ''; // entered in the skin editor
#		'SkinCode' => '';
#	);

$wgAstropayGatewayHtmlFormDir = __DIR__ . '/astropay_gateway/forms/html';
// Set base URLs here.  Individual transactions have their own paths
$wgAstropayGatewayURL = 'https://astropaycard.com/';
$wgAstropayGatewayTestingURL = 'https://sandbox.astropaycard.com/';
#	$wgAstropayGatewayAccountInfo['example'] = array(
#		'Create' => array( // For creating invoices
#			'Login' => '',
#			'Password' => '',
#		),
#		'Status' => array( // For checking payment status
#			'Login' => '',
#			'Password' => '',
#		),
#		'SecretKey' => '', // For signing requests and verifying responses
#	);

$wgWorldpayGatewayHtmlFormDir = __DIR__ . '/worldpay_gateway/forms/html';

$wgWorldpayGatewayURL = 'https://some.url.here';

/**
 * Set this to true if fraud checks should be disabled for integration testing
 */
$wgWorldpayGatewayNoFraudIntegrationTest = false;

/*
$wgWorldpayGatewayAccountInfo['default'] = array(
	'Test' => 1,
	'MerchantId' => 00000,
	'Username' => 'suchuser',
	'Password' => 'suchsecret',

	'DefaultCurrency' => CURRENCY

	'StoreIDs' => array(
		CURRENCY => StoreID
	),
);
*/

$wgWorldpayGatewayCvvMap = array (
	'0' => false, //No Match
	'1' => true, //Match
	'2' => false, //Not Checked
	'3' => false, //Issuer is Not Certified or Unregistered
	'4' => false, //Should have CVV2 on card - ??
	'5' => false, //CVC1 Incorrect
	'6' => false, //No service available.
	'7' => false, //No code returned. All the points.
	'8' => false, //No code returned. All the points.
	'9' => false, //Not Performed
	//(occurs when CVN value was not present in the STN string
	//or when transaction was not sent to the acquiring bank)
	'' => false, //No code returned. All the points.
);

$wgWorldpayGatewayAvsAddressMap = array (
	'0' => 50, //No Match
	'1' => 0, //Match
	'2' => 12, //Not Checked/Not Available
	'3' => 50, //Issuer is Not Certified or Unregistered
	'4' => 12, //Not Supported
	'9' => 12, //Not Performed (occurs when Address1, Address2 and Address3 values were not present in the STN string or when transaction was not sent to the acquiring bank)
	'' => 50, //No code returned. All the points.
);

$wgWorldpayGatewayAvsZipMap = array (
	'0' => 50, //No Match
	'1' => 0, //Match
	'2' => 12, //Not Checked/Not Available
	'3' => 0, //9 digit zipcode match
	'4' => 0, //5 digit zipcode match
	'5' => 12, //Not Supported
	'9' => 12, //Not Performed (occurs when ZipCode value was not present in the STN string or when transaction was not sent to the acquiring bank)
	'' => 50, //No code returned. All the points.
);

$wgStompServer = "";

// In this array, 'default', 'pending', and 'limbo' are required keys for those categories of
// transactions. The value is the name of the queue. To single out a transaction type, ie:
// credit cards, prepend 'cc-' to the base key name.
//
// If the resultant queue name evaluates to false, the message will not be queued on the server.
$wgStompQueueNames = array(
	'default' => 'test-default',    // Previously known as $wgStompQueueName
	'pending' => 'test-pending',    // Previously known as $wgPendingStompQueueName
	'limbo' => 'test-limbo', // Previously known as $wgLimboStompQueueName
	'payments-antifraud' => 'payments-antifraud', //noncritical: Basically shoving the fraud log into a database.
	'payments-init' => 'payments-init', //noncritical: same as above with the payments-initial log
);

/**
 * @global array $wgDonationInterfaceDefaultQueueServer
 *
 * Common development defaults for the queue server.
 * TODO: Default to a builtin backend such as PDO?
 * FIXME: Note that this must be an instance of FifoQueueStore.
 */
$wgDonationInterfaceDefaultQueueServer = array(
	'type' => 'PHPQueue\Backend\Stomp',
	'uri' => 'tcp://localhost:61613',
	'read_timeout' => '1',
	'expiry' => '30 days',
);

/**
 * @global array $wgDonationInterfaceQueues
 *
 * This is a mapping from queue name to attributes.  It's not necessary to
 * list queues here, but the built-in queues are listed for convenience.
 *
 * Default values are taken from $wgDonationInterfaceDefaultQueueServer, and
 * values given here will override the defaults.
 *
 * The array key is the queue name as it is referred to from code, although the
 * actual queue name used in the backend may be overridden, see below.
 *
 * Unrecognized options will be passed along to the queue backend constructor,
 * but the following have special meaning to DonationQueue:
 *     type - Class name of the queue backend.
 *     expiry - The default lifespan of messages in this queue (days).
 *     name - Backend can map to a named queue, rather than default to the
 *         queue key as it appears in the $wgDonationInterfaceQueues array.
 */
$wgDonationInterfaceQueues = array(
	// Incoming donations that we think have been paid for.
	'completed' => array(),
	// So-called limbo queue for GlobalCollect, where we store donor personal
	// information while waiting for the donor to return from iframe or a
	// redirect.  It's very important that this data is not stored anywhere
	// permanent such as logs or the database, until we know this person
	// finished making a donation.
	// FIXME: Note that this must be an instance of KeyValueStore.
	'globalcollect-cc-limbo' => array(
		'type' => 'PHPQueue\Backend\Memcache',
		'servers' => array( 'localhost' ),
		# 30 days, in seconds
		'expiry' => 2592000,
	),
	// The general limbo queue (see above). FIXME: deprecated?
	'limbo' => array(
		'type' => 'PHPQueue\Backend\Memcache',
		'servers' => array( 'localhost' ),
		'expiry' => 2592000,
	),
	// Where limbo messages go to die, if the orphan slayer decides they are
	// still in one of the pending states.  FIXME: who reads from this queue?
	'pending' => array(),

	// Non-critical queues

	// These messages will be shoved into the fraud database (see
	// crm/modules/fredge).
	'payments-antifraud' => array(),
	// These are shoved into the payments-initial database.
	'payments-init' => array(),
);

//Custom Filters globals
//Define the action to take for a given $risk_score
$wgDonationInterfaceCustomFiltersActionRanges = array(
	'process' => array( 0, 100 ),
	'review' => array( -1, -1 ),
	'challenge' => array( -1, -1 ),
	'reject' => array( -1, -1 ),
);

/**
 * A value for tracking the 'riskiness' of a transaction
 *
 * The action to take based on a transaction's riskScore is determined by
 * $action_ranges.  This is built assuming a range of possible risk scores
 * as 0-100, although you can probably bend this as needed.
 */
$wgDonationInterfaceCustomFiltersRiskScore = 0;

//Minfraud globals
/**
 * Your minFraud license key.
 */
$wgMinFraudLicenseKey = '';

/**
 * Set the risk score ranges that will cause a particular 'action'
 *
 * The keys to the array are the 'actions' to be taken (eg 'process').
 * The value for one of these keys is an array representing the lower
 * and upper bounds for that action.  For instance,
 *   $wgDonationInterfaceMinFraudActionRanges = array(
 * 		'process' => array( 0, 100)
 * 		...
 * 	);
 * means that any transaction with a risk score greather than or equal
 * to 0 and less than or equal to 100 will be given the 'process' action.
 *
 * These are evauluated on a >= or <= basis.  Please refer to minFraud
 * documentation for a thorough explanation of the 'riskScore'.
 */
$wgDonationInterfaceMinFraudActionRanges = array(
	'process' => array( 0, 100 ),
	'review' => array( -1, -1 ),
	'challenge' => array( -1, -1 ),
	'reject' => array( -1, -1 )
);

/**
 * This allows setting where to point the minFraud servers.
 *
 * As of February 21st, 2012 minfraud.maxmind.com will route to the east or
 * west server, depending on you location.
 *
 * minfraud-us-east.maxmind.com: 174.36.207.186
 * minfraud-us-west.maxmind.com: 50.97.220.226
 *
 * The minFraud API requires an array of servers.
 *
 * You do not have to specify a server.
 *
 * @see CreditCardFraudDetection::$server
 */
$wgDonationInterfaceMinFraudServers = array();

// Timeout in seconds for communicating with MaxMind
$wgMinFraudTimeout = 2;

/**
 * When to send an email to $wgEmergencyContact that we're
 * running low on minfraud queries. Will continue to send
 * once per day until the limit is once again over the limit.
 */
$wgDonationInterfaceMinFraudAlarmLimit = 25000;

//Referrer Filter globals
$wgDonationInterfaceCustomFiltersRefRules = array();

//Source Filter globals
$wgDonationInterfaceCustomFiltersSrcRules = array();

//Functions Filter globals
$wgDonationInterfaceCustomFiltersFunctions = array();

//IP velocity filter globals
$wgDonationInterfaceMemcacheHost = 'localhost';
$wgDonationInterfaceMemcachePort = '11211';
$wgDonationInterfaceIPVelocityFailScore = 100;
$wgDonationInterfaceIPVelocityTimeout = 60 * 5;	//5 minutes in seconds
$wgDonationInterfaceIPVelocityThreshhold = 3;	//3 transactions per timeout
//$wgDonationInterfaceIPVelocityToxicDuration can be set to penalize IP addresses
//that attempt to use cards reported stolen.
//$wgDonationInterfaceIPVelocityFailDuration is also something you can set...
//If you leave it blank, it will use the VelocityTimeout as a default.

// Session velocity filter globals
$wgDonationInterfaceSessionVelocity_HitScore = 10;  // How much to add to the score per API hit
$wgDonationInterfaceSessionVelocity_DecayRate = 1;  // Linear decay rate; pts / sec
$wgDonationInterfaceSessionVelocity_Threshold = 50; // Above this score, we deny users the page

/**
 * $wgDonationInterfaceCountryMap
 *
 * A score of 0 for a country means no risk.
 * A score of 100 means this country is extremely risky for fraud.
 *
 * The score for a country has the following range:
 *
 * 0 <= $score <= 100
 *
 * To enable this filter add this to your LocalSettings.php:
 *
 * @code
 * <?php
 *
 * $wgCustomFiltersFunctions = array(
 * 	'getScoreCountryMap' => 100,
 * );
 *
 * $wgDonationInterfaceCountryMap = array(
 * 	'CA' =>  1,
 * 	'US' => 5,
 * );
 * ?>
 * @endcode
 */
$wgDonationInterfaceCountryMap = array();

/**
 * $wgDonationInterfaceEmailDomainMap
 *
 * A score of 0 for an email domain means no risk.
 * A score of 100 means this email domain is extremely risky for fraud.
 * Scores may be negative.
 *
 * To enable this filter add this to your LocalSettings.php:
 *
 * @code
 * <?php
 *
 * $wgCustomFiltersFunctions = array(
 * 	'getScoreEmailDomainMap' => 100,
 * );
 *
 * $wgDonationInterfaceEmailDomainMap = array(
 * 	'gmail.com' =>  5,
 * 	'wikimedia.org' => 0,
 * );
 * ?>
 * @endcode
 */
$wgDonationInterfaceEmailDomainMap = array();

/**
 * $wgDonationInterfaceUtmCampaignMap
 *
 * A score of 0 for utm_campaign means no risk.
 * A score of 100 means this utm_campaign is extremely risky for fraud.
 * Scores may be negative
 *
 * To enable this filter add this to your LocalSettings.php:
 *
 * @code
 * <?php
 *
 * $wgCustomFiltersFunctions = array(
 * 	'getScoreUtmCampaignMap' => 100,
 * );
 *
 * $wgDonationInterfaceUtmCampaignMap = array(
 * 	'' =>  20,
 * 	'some-odd-string' => 100,
 * );
 * ?>
 * @endcode
 */
$wgDonationInterfaceUtmCampaignMap = array();

/**
 * $wgDonationInterfaceUtmMediumMap
 *
 * A score of 0 for utm_medium means no risk.
 * A score of 100 means this utm_medium is extremely risky for fraud.
 * Scores may be negative
 *
 * To enable this filter add this to your LocalSettings.php:
 *
 * @code
 * <?php
 *
 * $wgCustomFiltersFunctions = array(
 * 	'getScoreUtmMediumMap' => 100,
 * );
 *
 * $wgDonationInterfaceUtmMediumMap = array(
 * 	'' =>  20,
 * 	'some-odd-string' => 100,
 * );
 * ?>
 * @endcode
 */
$wgDonationInterfaceUtmMediumMap = array();

/**
 * $wgDonationInterfaceUtmSourceMap
 *
 * A score of 0 for utm_source means no risk.
 * A score of 100 means this utm_source is extremely risky for fraud.
 * Scores may be negative
 *
 * To enable this filter add this to your LocalSettings.php:
 *
 * @code
 * <?php
 *
 * $wgCustomFiltersFunctions = array(
 * 	'getScoreUtmSourceMap' => 100,
 * );
 *
 * $wgDonationInterfaceUtmSourceMap = array(
 * 	'' =>  20,
 * 	'some-odd-string' => 100,
 * );
 * ?>
 * @endcode
 */
$wgDonationInterfaceUtmSourceMap = array();

$wgDonationInterfaceEnableStomp = false;
$wgDonationInterfaceEnableQueue = false;
$wgDonationInterfaceEnableConversionLog = false; //this is definitely an Extra
$wgDonationInterfaceEnableMinfraud = false; //this is definitely an Extra

$wgGlobalCollectGatewayEnabled = false;
$wgAmazonGatewayEnabled = false;
$wgAdyenGatewayEnabled = false;
$wgAstropayGatewayEnabled = false;
$wgPaypalGatewayEnabled = false;
$wgWorldpayGatewayEnabled = false;

/**
 * @global boolean Set to false to disable all filters, or set a gateway-
 * specific value such as $wgPaypalGatewayEnableCustomFilters = false.
 */
$wgDonationInterfaceEnableCustomFilters = true;

$wgDonationInterfaceEnableFormChooser = false;
$wgDonationInterfaceEnableReferrerFilter = false; //extra
$wgDonationInterfaceEnableSourceFilter = false; //extra
$wgDonationInterfaceEnableFunctionsFilter = false; //extra
$wgDonationInterfaceEnableIPVelocityFilter = false; //extra
$wgDonationInterfaceEnableSessionVelocityFilter = false; //extra
$wgDonationInterfaceEnableSystemStatus = false; //extra

$wgSpecialPages['GatewayFormChooser'] = 'GatewayFormChooser';
$wgSpecialPages['SystemStatus'] = 'SystemStatus';

$wgSpecialPages['GlobalCollectGateway'] = 'GlobalCollectGateway';
$wgSpecialPages['GlobalCollectGatewayResult'] = 'GlobalCollectGatewayResult';
$wgDonationInterfaceGatewayAdapters[] = 'GlobalCollectAdapter';

$wgSpecialPages['AmazonGateway'] = 'AmazonGateway';
$wgDonationInterfaceGatewayAdapters[] = 'AmazonAdapter';

$wgSpecialPages['AdyenGateway'] = 'AdyenGateway';
$wgSpecialPages['AdyenGatewayResult'] = 'AdyenGatewayResult';
$wgDonationInterfaceGatewayAdapters[] = 'AdyenAdapter';

$wgSpecialPages['AstropayGateway'] = 'AstropayGateway';
$wgSpecialPages['AstropayGatewayResult'] = 'AstropayGatewayResult';
$wgDonationInterfaceGatewayAdapters[] = 'AstropayAdapter';

$wgSpecialPages['PaypalGateway'] = 'PaypalGateway';
$wgSpecialPages['PaypalGatewayResult'] = 'PaypalGatewayResult';
$wgDonationInterfaceGatewayAdapters[] = 'PaypalAdapter';

$wgSpecialPages['WorldpayGateway'] = 'WorldpayGateway';
$wgDonationInterfaceGatewayAdapters[] = 'WorldpayAdapter';

//Stomp hooks
// FIXME: There's no point in using hooks any more, since we're switching
// behavior inside the callbacks and not via conditional hooking.
$wgHooks['ParserFirstCallInit'][] = 'efStompSetup';
$wgHooks['gwStomp'][] = 'sendSTOMP';
$wgHooks['gwPendingStomp'][] = 'sendPendingSTOMP';
$wgHooks['gwFreeformStomp'][] = 'sendFreeformSTOMP';

//Custom Filters hooks
$wgHooks['GatewayValidate'][] = array( 'Gateway_Extras_CustomFilters::onValidate' );

$wgHooks['GatewayCustomFilter'][] = array( 'Gateway_Extras_CustomFilters_Referrer::onFilter' );
$wgHooks['GatewayCustomFilter'][] = array( 'Gateway_Extras_CustomFilters_Source::onFilter' );
$wgHooks['GatewayCustomFilter'][] = array( 'Gateway_Extras_CustomFilters_Functions::onFilter' );
$wgHooks['GatewayCustomFilter'][] = array( 'Gateway_Extras_CustomFilters_MinFraud::onFilter' );
$wgHooks['GatewayCustomFilter'][] = array( 'Gateway_Extras_CustomFilters_IP_Velocity::onFilter' );
$wgHooks['GatewayPostProcess'][] = array( 'Gateway_Extras_CustomFilters_IP_Velocity::onPostProcess' );

$wgHooks['DonationInterfaceCurlInit'][] = array( 'Gateway_Extras_SessionVelocityFilter::onCurlInit' );

//Conversion Log hooks
// Sets the 'conversion log' as logger for post-processing
$wgHooks['GatewayPostProcess'][] = array( 'Gateway_Extras_ConversionLog::onPostProcess' );

//Unit tests
$wgHooks['UnitTestsList'][] = 'efDonationInterfaceUnitTests';

/**
 * APIS
 */
// enable the API
$wgAPIModules['donate'] = 'DonationApi';
$wgAutoloadClasses['DonationApi'] = __DIR__ . '/gateway_common/donation.api.php';


/**
 * ADDITIONAL MAGICAL GLOBALS
 */

// Resource modules
$wgResourceTemplate = array(
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'DonationInterface/modules',
);
$wgResourceModules['iframe.liberator'] = array(
	'scripts' => 'iframe.liberator.js',
	'position' => 'top'
	) + $wgResourceTemplate;

$wgResourceModules['donationInterface.skinOverride'] = array(
	'scripts' => 'js/skinOverride.js',
	'styles' => array(
		'css/gateway.css',
		'css/skinOverride.css',
	),
	'position' => 'top'
	) + $wgResourceTemplate;

$wgResourceModules['donationInterface.test.rapidhtml'] = array(
	'scripts' => 'tests/modules/gc.testinterface.js',
	'dependencies' => array(
		'mediawiki.Uri',
		'gc.normalinterface'
	)
) + $wgResourceTemplate;

$wgResourceModules['jquery.payment'] = array(
	'scripts' => 'jquery.payment/jquery.payment.js',
) + $wgResourceTemplate;

//Forms
$wgResourceModules['ext.donationinterface.mustache.styles'] = array (
	'styles' => array(
		'forms.css'
	),
	'localBasePath' => __DIR__ . '/gateway_forms/mustache',
	'remoteExtPath' => 'DonationInterface/gateway_forms/mustache',
	'position' => 'top',
);

$wgResourceModules['ext.donationinterface.mustache.scripts'] = array (
	'scripts' => 'forms.js',
	'dependencies' => 'di.form.core.validate',
	'localBasePath' => __DIR__ . '/gateway_forms/mustache',
	'remoteExtPath' => 'DonationInterface/gateway_forms/mustache'
);

// load any rapidhtml related resources
require_once( __DIR__ . '/gateway_forms/rapidhtml/RapidHtmlResources.php' );


$wgResourceTemplate = array(
	'localBasePath' => __DIR__ . '/gateway_forms',
	'remoteExtPath' => 'DonationInterface/gateway_forms',
);

$wgResourceModules[ 'ext.donationInterface.errorMessages' ] = array(
	'messages' => array(
		'donate_interface-noscript-msg',
		'donate_interface-noscript-redirect-msg',
		'donate_interface-error-msg-general',
		'donate_interface-error-msg',
		'donate_interface-error-msg-js',
		'donate_interface-error-msg-validation',
		'donate_interface-error-msg-invalid-amount',
		'donate_interface-error-msg-email',
		'donate_interface-error-msg-card-num',
		'donate_interface-error-msg-amex',
		'donate_interface-error-msg-mc',
		'donate_interface-error-msg-visa',
		'donate_interface-error-msg-discover',
		'donate_interface-error-msg-amount',
		'donate_interface-error-msg-emailAdd',
		'donate_interface-error-msg-fname',
		'donate_interface-error-msg-lname',
		'donate_interface-error-msg-street',
		'donate_interface-error-msg-city',
		'donate_interface-error-msg-state',
		'donate_interface-error-msg-zip',
		'donate_interface-error-msg-postal',
		'donate_interface-error-msg-country',
		'donate_interface-error-msg-card_type',
		'donate_interface-error-msg-card_num',
		'donate_interface-error-msg-expiration',
		'donate_interface-error-msg-cvv',
		'donate_interface-error-msg-fiscal_number',
		'donate_interface-error-msg-captcha',
		'donate_interface-error-msg-captcha-please',
		'donate_interface-error-msg-cookies',
		'donate_interface-error-msg-account_name',
		'donate_interface-error-msg-account_number',
		'donate_interface-error-msg-authorization_id',
		'donate_interface-error-msg-bank_check_digit',
		'donate_interface-error-msg-bank_code',
		'donate_interface-error-msg-branch_code',
		'donate_interface-smallamount-error',
		'donate_interface-donor-fname',
		'donate_interface-donor-lname',
		'donate_interface-donor-street',
		'donate_interface-donor-city',
		'donate_interface-donor-state',
		'donate_interface-donor-zip',
		'donate_interface-donor-postal',
		'donate_interface-donor-country',
		'donate_interface-donor-emailAdd',
		'donate_interface-state-province',
		'donate_interface-cvv-explain',
	)
);

// minimum amounts for all currencies
$wgResourceModules[ 'di.form.core.minimums' ] = array(
	'scripts' => 'validate.currencyMinimums.js',
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'DonationInterface/modules'
);

// form validation resource
$wgResourceModules[ 'di.form.core.validate' ] = array(
	'scripts' => 'validate_input.js',
	'dependencies' => array( 'di.form.core.minimums', 'ext.donationInterface.errorMessages' ),
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'DonationInterface/modules'
);


// Load the interface messages that are shared across multiple gateways
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/interface';
$wgExtensionMessagesFiles['DonateInterface'] = __DIR__ . '/gateway_common/interface.i18n.php';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/country-specific';
$wgExtensionMessagesFiles['DonateInterfaceAlt'] = __DIR__ . '/gateway_common/country.specific.i18n.php';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/countries';
$wgExtensionMessagesFiles['GatewayCountries'] = __DIR__ . '/gateway_common/countries.i18n.php';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/us-states';
$wgExtensionMessagesFiles['GatewayUSStates'] = __DIR__ . '/gateway_common/us-states.i18n.php';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/canada-provinces';
$wgExtensionMessagesFiles['GatewayCAProvinces'] = __DIR__ . '/gateway_common/canada-provinces.i18n.php';
$wgExtensionMessagesFiles['GatewayAliases'] = __DIR__ . '/DonationInterface.alias.php';

$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/amazon_gateway/i18n';
$wgExtensionMessagesFiles['AmazonGateway'] = __DIR__ . '/amazon_gateway/amazon_gateway.i18n.php';
$wgExtensionMessagesFiles['AmazonGatewayAlias'] = __DIR__ . '/amazon_gateway/amazon_gateway.alias.php';

//GlobalCollect gateway magical globals
// @todo All the bits where we make the i18n make sense for multiple gateways. This is clearly less than ideal.
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/globalcollect_gateway/i18n';
$wgExtensionMessagesFiles['GlobalCollectGateway'] = __DIR__ . '/globalcollect_gateway/globalcollect_gateway.i18n.php';
$wgExtensionMessagesFiles['GlobalCollectGatewayAlias'] = __DIR__ . '/globalcollect_gateway/globalcollect_gateway.alias.php';

$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/adyen_gateway/i18n';
$wgExtensionMessagesFiles['AdyenGateway'] = __DIR__ . '/adyen_gateway/adyen_gateway.i18n.php';
$wgExtensionMessagesFiles['AdyenGatewayAlias'] = __DIR__ . '/adyen_gateway/adyen_gateway.alias.php';

$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/astropay_gateway/i18n';
$wgExtensionMessagesFiles['AstropayGateway'] = __DIR__ . '/astropay_gateway/astropay_gateway.i18n.php';
$wgExtensionMessagesFiles['AstropayGatewayAlias'] = __DIR__ . '/astropay_gateway/astropay_gateway.alias.php';

$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/paypal_gateway/i18n';
$wgExtensionMessagesFiles['PaypalGateway'] = __DIR__ . '/paypal_gateway/paypal_gateway.i18n.php';
$wgExtensionMessagesFiles['PaypalGatewayAlias'] = __DIR__ . '/paypal_gateway/paypal_gateway.alias.php';

$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/worldpay_gateway/i18n';
$wgExtensionMessagesFiles['WorldpayGateway'] = __DIR__ . '/worldpay_gateway/worldpay_gateway.i18n.php';
$wgExtensionMessagesFiles['WorldpayGatewayAlias'] = __DIR__ . '/worldpay_gateway/worldpay_gateway.alias.php';

/**
 * See default values in DonationInterfaceFormSettings.php.  Note that any values
 * set in LocalSettings.php are array_merged into the defaults, which allows you
 * to override specific forms.  Please completely specify forms when overriding,
 * or disable by setting to an empty array or false.
 */
$wgDonationInterfaceAllowedHtmlForms = array();

/**
 * Base directories for each gateway's form templates.
 */
$wgDonationInterfaceFormDirs = array(
	'adyen' => $wgAdyenGatewayHtmlFormDir,
	'amazon' => $wgAmazonGatewayHtmlFormDir,
	'astropay' => $wgAstropayGatewayHtmlFormDir,
	'default' => $wgDonationInterfaceHtmlFormDir,
	'gc' => $wgGlobalCollectGatewayHtmlFormDir,
	'paypal' => $wgPaypalGatewayHtmlFormDir,
	'worldpay' => $wgWorldpayGatewayHtmlFormDir,
);

// Load the default form settings.
require_once __DIR__ . '/DonationInterfaceFormSettings.php';

/**
 * FUNCTIONS
 */

//---Stomp functions---
// TODO: Encapsulate in a class, or deprecate.
require_once( __DIR__ . '/activemq_stomp/activemq_stomp.php'  );
$wgAutoloadClasses['Stomp'] = __DIR__ . '/activemq_stomp/Stomp.php';

function efDonationInterfaceUnitTests( &$files ) {
	global $wgAutoloadClasses;

	$testDir = __DIR__ . '/tests/';

	$files[] = $testDir . 'AllTests.php';

	$wgAutoloadClasses['DonationInterfaceTestCase'] = $testDir . 'DonationInterfaceTestCase.php';
	$wgAutoloadClasses['TestingQueue'] = $testDir . 'includes/TestingQueue.php';
	$wgAutoloadClasses['TestingAdyenAdapter'] = $testDir . 'includes/test_gateway/TestingAdyenAdapter.php';
	$wgAutoloadClasses['TestingAmazonAdapter'] = $testDir . 'includes/test_gateway/TestingAmazonAdapter.php';
	$wgAutoloadClasses['TestingAstropayAdapter'] = $testDir . 'includes/test_gateway/TestingAstropayAdapter.php';
	$wgAutoloadClasses['TestingAmazonGateway'] = $testDir . 'includes/test_page/TestingAmazonGateway.php';
	$wgAutoloadClasses['TestingDonationLogger'] = $testDir . 'includes/TestingDonationLogger.php';
	$wgAutoloadClasses['TestingGatewayPage'] = $testDir . 'includes/TestingGatewayPage.php';
	$wgAutoloadClasses['TestingGenericAdapter'] = $testDir . 'includes/test_gateway/TestingGenericAdapter.php';
	$wgAutoloadClasses['TestingGlobalCollectAdapter'] = $testDir . 'includes/test_gateway/TestingGlobalCollectAdapter.php';
	$wgAutoloadClasses['TestingGlobalCollectGateway'] = $testDir . 'includes/test_page/TestingGlobalCollectGateway.php';
	$wgAutoloadClasses['TestingGlobalCollectOrphanAdapter'] = $testDir . 'includes/test_gateway/TestingGlobalCollectOrphanAdapter.php';
	$wgAutoloadClasses['TestingPaypalAdapter'] = $testDir . 'includes/test_gateway/TestingPaypalAdapter.php';
	$wgAutoloadClasses['TestingWorldpayAdapter'] = $testDir . 'includes/test_gateway/TestingWorldpayAdapter.php';
	$wgAutoloadClasses['TestingWorldpayGateway'] = $testDir . 'includes/test_page/TestingWorldpayGateway.php';

	$wgAutoloadClasses['TestingLanguage'] = $testDir . 'includes/test_language/test.language.php';
	$wgAutoloadClasses['TestingRequest'] = $testDir . 'includes/test_request/test.request.php';

	return true;
}

// Include composer's autoload if the vendor directory exists.  If we have been
// included via Composer, our dependencies should already be autoloaded at the
// top level.
// Note that in WMF's continuous integration, we can still only use stuff from
// Composer if it is already in Mediawiki's vendor directory, such as monolog
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $vendorAutoload ) ) {
	require_once ( $vendorAutoload );
} else {
	require_once ( 'gateway_common/WmfFramework.php' );
}
