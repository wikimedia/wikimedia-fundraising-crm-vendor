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
 * GatewayType Interface
 *
 */
interface GatewayType {
	//all the particulars of the child classes. Aaaaall.

	/**
	 * Process the API response obtained from the payment processor and set
	 * properties of transaction_response
	 * @param array|DomDocument $response Cleaned-up response returned from
	 *        @see getFormattedResponse.  Type depends on $this->getResponseType
	 * @throws ResponseProcessingException with an actionable error code and any
	 *         variables to retry
	 */
	public function processResponse( $response );

	/**
	 * Should be a list of our variables that need special staging.
	 * @see $this->staged_vars
	 */
	function defineStagedVars();

	/**
	 * defineTransactions will define the $transactions array.
	 * The array will contain everything we need to know about the request structure for all the transactions we care about,
	 * for the current gateway.
	 * First array key: Some way for us to id the transaction. Doesn't actually have to be the gateway's name for it, but I'm going with that until I have a reason not to.
	 * Second array key:
	 * 		'request' contains the structure of that request. Leaves in the array tree will eventually be mapped to actual values of ours,
	 * 		according to the precidence established in the getTransactionSpecificValue function.
	 * 		'values' contains default values for the transaction. Things that are typically not overridden should go here.
	 */
	function defineTransactions();

	/**
	 * Define the message keys used to display errors to the user.  Should set
	 * @see $this->error_map to an array whose keys are error codes and whose
	 * values are i18n keys.
	 * Any unmapped error code will use 'donate_interface-processing-error'
	 */
	function defineErrorMap();

	/**
	 * defineVarMap needs to set up the $var_map array.
	 * Keys = the name (or node name) value in the gateway transaction
	 * Values = the mediawiki field name for the corresponding piece of data.
	 */
	function defineVarMap();

	/**
	 */
	function defineDataConstraints();

	/**
	 * defineAccountInfo needs to set up the $accountInfo array.
	 * Keys = the name (or node name) value in the gateway transaction
	 * Values = The actual values for those keys. Probably have to access a global or two. (use getGlobal()!)
	 */
	function defineAccountInfo();

	/**
	 * defineReturnValueMap sets up the $return_value_map array.
	 * Keys = The different constants that may be contained as values in the gateway's response.
	 * Values = what that string constant means to mediawiki.
	 */
	function defineReturnValueMap();

	/**
	 * Sets up the $payment_methods array.
	 * Keys = unique name for this method
	 * Values = metadata about the method
	 */
	function definePaymentMethods();

	/**
	 * Sets up the $order_id_meta array.
	 * @TODO: Data Item Class. There should be a class that keeps track of
	 * the metadata for every field we use (everything that currently comes
	 * back from DonationData), that can be overridden per gateway. Revisit
	 * this in a more universal way when that time comes.
	 *
	 * In general, $order_id_meta contains default data about how we
	 * handle/create/gather order_id, which needs to be defined on a
	 * per-gateway basis. Once $order_id_meta has been used to decide the
	 * order_id for the current request, it will also be used to keep
	 * information about the origin and state of the order_id data.
	 *
	 * Should contain the following keys/values:
	 * 'alt_locations' => array( $dataset_name, $dataset_key )
	 *	** alt_locations is intended to contain a list of arrays that
	 *	are always available (or should be), from which we can pull the
	 *	order_id.
	 *	** Examples of valid things to throw in $dataset_name are $_GET,
	 *	$_POST, $_SESSION (though they should be expressed in the arary
	 *	without the dollar prefix)
	 *	** $dataset_key : The key in the associated dataset that is
	 *	expected to contain the order_id. Probably going to be order_id
	 *	if we are generating the dataset internally. Probably something
	 *	else if a gateway is posting or getting back to us in a
	 *	resultswitcher situation.
	 *	** These should be expressed in $order_id_meta in order of
	 *	preference / authority.
	 * 'generate' => boolean value. True if we will be generating our own
	 *	order IDs, false if we are deferring order_id generation to the
	 *	gateway.
	 * 'ct_id' => boolean value.  If True, when generating order ID use
	 * the contribution tracking ID with the sequence number appended
	 *
	 * Will eventually contain the following keys/values:
	 * 'final'=> The value that we have chosen as the valid order ID for
	 *	this request.
	 * 'final_source' => Where we ultimately decided to grab the value we
	 *	chose to stuff in 'final'.
	 */
	function defineOrderIDMeta();

	/**
	 * Called in the constructor, this function should be used to define
	 * pieces of default data particular to the gateway. It will be up to
	 * the child class to poke the data through to the data object
	 * (probably with $this->addRequestData()).
	 * DO NOT set default payment information here (or anywhere, really).
	 * That would be naughty.
	 */
	function setGatewayDefaults();

	/**
	 * @return array of ISO 4217 currency codes supported by this adapter
	 */
	static function getCurrencies();

	/**
	 * Attempt the default transaction for the current DonationData
	 *
	 * @return PaymentResult hints for the next donor interaction
	 */
	function doPayment();

	/**
	 * Data format for outgoing requests to the processor.
	 * Must be one of 'xml', 'namevalue' (for POST), or 'redirect'.
	 * May depend on current transaction.
	 *
	 * @return string
	 */
	function getCommunicationType();

	/**
	 * Data format for responses coming back from the processor.
	 * Should be 'xml' // TODO: json
	 *
	 * @return string
	 */
	function getResponseType();
}

interface LogPrefixProvider {
	function getLogMessagePrefix();
}
/**
 * GatewayAdapter
 *
 */
abstract class GatewayAdapter implements GatewayType, LogPrefixProvider {

	/**
	 * $dataConstraints provides information on how to handle variables.
	 *
	 * 	 <code>
	 * 		'account_holder'		=> array( 'type' => 'alphanumeric',		'length' => 50, )
	 * 	 </code>
	 *
	 * @var	array	$dataConstraints
	 */
	protected $dataConstraints = array();

	/**
	 * $error_map maps gateway errors to client errors
	 *
	 * The key of each error should map to a i18n message key.
	 * By convention, the following three keys have these meanings:
	 *   'internal-0000' => 'message-key-1', // Failed failed pre-process checks.
	 *   'internal-0001' => 'message-key-2', // Transaction could not be processed due to an internal error.
	 *   'internal-0002' => 'message-key-3', // Communication failure
	 * Any undefined key will map to 'donate_interface-processing-error'
	 *
	 * @var	array	$error_map
	 */
	protected $error_map = array();

	/**
	 * @see GlobalCollectAdapter::defineGoToThankYouOn()
	 *
	 * @var	array	$goToThankYouOn
	 */
	protected $goToThankYouOn = array();

	/**
	 * $var_map maps gateway variables to client variables
	 *
	 * @var	array	$var_map
	 */
	protected $var_map = array();

	protected $account_name;
	protected $account_config;
	protected $accountInfo;
	protected $url;
	protected $transactions;

	/**
	 * $payment_methods will be defined by the adapter.
	 *
	 * @var	array	$payment_methods
	 */
	protected $payment_methods = array();

	/**
	 * $payment_submethods will be defined by the adapter.
	 *
	 * @var	array	$payment_submethods
	 */
	protected $payment_submethods = array();

	/**
	 * Staged variables. This is affected by the transaction type.
	 *
	 * @var array $staged_vars
	 */
	protected $staged_vars = array();
	protected $return_value_map;
	protected $staged_data;
	protected $unstaged_data;

	/**
	 * For gateways that speak XML, we use this variable to hold the document
	 * while we build the outgoing request.  TODO: move XML functions out of the
	 * main gateway classes.
	 * @var DomDocument
	 */
	protected $xmlDoc;

	/**
	 * @var DonationData
	 */
	protected $dataObj;

	/**
	 * Standard logger, logs to {type}_gateway
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Logs to {type}_gateway_commstats
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $commstats_logger;

	/**
	 * Logs to {type}_gateway_payment_init
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $payment_init_logger;

	/**
	 * $transaction_response is the member var that keeps track of the results of
	 * the latest discrete transaction with the gateway.
	 * @var PaymentTransactionResponse
	 */
	protected $transaction_response;
	/**
	 * @var string When the smoke clears, this should be set to one of the
	 * constants defined in @see FinalStatus
	 */
	protected $final_status;
	protected $validation_errors;
	protected $manual_errors = array();

	/**
	 * Name of the current transaction.  Set via @see setCurrentTransaction
	 * @var string
	 */
	protected $current_transaction;
	protected $action;
	protected $risk_score = 0;
	public $debugarray;
	/**
	 * A boolean that will tell us if we've posted to ourselves. A little more telling than
	 * $wgRequest->wasPosted(), as something else could have posted to us.
	 * @var boolean
	 */
	public $posted = false;
	protected $batch = false;
	protected $api_request = false;
	/**
	 * Holds the global values we've already looked up.  Used in getGlobal.
	 * @staticvar array
	 */
	protected static $globalsCache = array ( );

	//ALL OF THESE need to be redefined in the children. Much voodoo depends on the accuracy of these constants.
	const GATEWAY_NAME = 'Donation Gateway';
	const IDENTIFIER = 'donation';
	const GLOBAL_PREFIX = 'wgDonationGateway'; //...for example.

	public $log_outbound = FALSE; //This should be set to true for gateways that don't return the request in the response. @see buildLogXML()

	/**
	 * Default response type to be the same as communication type.
	 * @return string
	 */
	public function getResponseType() {
		return $this->getCommunicationType();
	}

	/**
	 * Get @see GatewayAdapter::$goToThankYouOn
	 */
	public function getGoToThankYouOn() {

		return $this->goToThankYouOn;
	}

	/**
	 * Constructor
	 *
	 * @param array	$options
	 *   OPTIONAL - You may set options for testing
	 *   - external_data - array, data from unusual sources (such as test fixture)
	 *   - api_request - Boolean, this is an api request, do not perform UI actions
	 *
	 * @see DonationData
	 */
	public function __construct( $options = array() ) {
		global $wgRequest;

		$defaults = array(
			'external_data' => null,
			'api_request' => false,
		);
		$options = array_merge( $defaults, $options );
		if ( array_key_exists( 'batch_mode', $options ) ) {
			$this->batch = $options['batch_mode'];
			unset( $options['batch_mode'] );
		}

		$this->logger = DonationLoggerFactory::getLogger( $this );
		$this->commstats_logger = DonationLoggerFactory::getLogger( $this, '_commstats' );
		$this->payment_init_logger = DonationLoggerFactory::getLogger( $this, '_payment_init' );

		if ( !self::getGlobal( 'Test' ) ) {
			$this->url = self::getGlobal( 'URL' );
		} else {
			$this->url = self::getGlobal( 'TestingURL' );
		}

		//so we know we can skip all the visual stuff.
		if ( $options['api_request'] ) {
			$this->setApiRequest();
		}

		// The following needs to be set up before we initialize DonationData.
		// TODO: move the rest of the initialization here
		$this->defineOrderIDMeta();
		$this->defineDataConstraints();
		$this->definePaymentMethods();

		$this->session_resetOnSwitch(); // Need to do this before creating DonationData

		// FIXME: this should not have side effects like setting order_id_meta['final']
		$this->dataObj = new DonationData( $this, $options['external_data'] );

		$this->setValidationErrors( $this->getOriginalValidationErrors() );

		$this->unstaged_data = $this->dataObj->getDataEscaped();
		$this->staged_data = $this->unstaged_data;

		//checking to see if we have an edit token in the request...
		$this->posted = ( $this->dataObj->wasPosted() && (!is_null( $wgRequest->getVal( 'token', null ) ) ) );

		$this->findAccount();
		$this->defineAccountInfo();
		$this->defineTransactions();
		$this->defineErrorMap();
		$this->defineVarMap();
		$this->defineReturnValueMap();
		$this->setValidForm();

		$this->setGatewayDefaults( $options );
		$this->stageData();
	}

	/**
	 * Determine which account to use for this session
	 */
	protected function findAccount() {
		$acctConfig = self::getGlobal( 'AccountInfo' );

		//this is causing warns in Special:SpecialPages
		if ( !$acctConfig ) {
			return;
		}

		//TODO crazy logic to determine which account we want
		$accounts = array_keys( $acctConfig );
		$this->account_name = array_shift( $accounts );

		$this->account_config = $acctConfig[ $this->account_name ];

		$this->addRequestData( array(
			'gateway_account' => $this->account_name,
		) );
	}

	/**
	 * Get the log message prefix:
	 * $contribution_tracking_id . ':' . $order_id . ' '
	 *
	 * Now, going to the DonationData object to handle this, because it will
	 * always have less stale data (and we need messages to come out of
	 * there before data exists here)
	 *
	 * @return string
	 */
	public function getLogMessagePrefix() {
		if ( !is_object( $this->dataObj ) ) {
			//please avoid exploding; It's just a log line.
			return 'Constructing! ';
		}
		return $this->dataObj->getLogMessagePrefix();
	}

	/**
	 * getThankYouPage should either return a full page url, or false.
	 * @return mixed Page URL in string format, or false if none is set.
	 */
	public function getThankYouPage() {
		$page = self::getGlobal( "ThankYouPage" );
		if ( $page ) {
			$page = $this->appendLanguageAndMakeURL( $page );
		}
		return $page;
	}

	/**
	 * @return string full Page URL
	 */
	public function getFailPage() {
		//Prefer RapidFail.
		if ( self::getGlobal( 'RapidFail' ) ) {
			$data = $this->getData_Unstaged_Escaped();

			//choose which fail page to go for.
			try {
				$fail_ffname = GatewayFormChooser::getBestErrorForm( $data['gateway'], $data['payment_method'], $data['payment_submethod'] );
				return GatewayFormChooser::buildPaymentsFormURL( $fail_ffname, $this->getRetryData() );
			} catch ( Exception $e ) {
				$this->logger->error( 'Cannot determine best error form. ' . $e->getMessage() );
			}
		}
		$page = self::getGlobal( 'FailPage' );
		if ( filter_var( $page, FILTER_VALIDATE_URL ) ) {
			return $this->appendLanguageAndMakeURL( $page );
		}

		// FIXME: either add Special:FailPage to avoid depending on wiki content,
		// or update the content on payments to be consistent with the /lang
		// format of ThankYou pages so we can use appendLanguageAndMakeURL here.
		$failTitle = Title::newFromText( $page );
		$language = $this->getData_Unstaged_Escaped( 'language' );
		$url = wfAppendQuery( $failTitle->getFullURL(), array( 'uselang' => $language ) );

		return $url;
	}

	/**
	 * For pages we intend to redirect to. This function will take either a full
	 * URL or a page title, and turn it into a URL with the appropriate language
	 * appended onto the end.
	 * @param string $url Either a wiki page title, or a URL to an external wiki
	 * page title.
	 * @return string A URL
	 */
	protected function appendLanguageAndMakeURL( $url ){
		$language = $this->getData_Unstaged_Escaped( 'language' );
		//make sure we don't already have the language in there...
		$dirs = explode('/', $url);
		if ( !is_array($dirs) || !in_array( $language, $dirs ) ){
			$url = $url . "/$language";
		}

		if ( strpos( $url, 'http' ) === 0) {
			return $url;
		} else { //this isn't a url yet.
			$returnTitle = Title::newFromText( $url );
			$url = $returnTitle->getFullURL();
			return $url;
		}
	}

	/**
	 * Checks the edit tokens in the user's session against the one gathered
	 * from populated form data.
	 * Adds a string to the debugarray, to make it a little easier to tell what
	 * happened if we turn the debug results on.
	 * Only called from the .body pages
	 * @return boolean true if match, else false.
	 */
	public function checkTokens() {
		$checkResult = $this->token_checkTokens();

		if ( $checkResult ) {
			$this->debugarray[] = 'Token Match';
		} else {
			$this->debugarray[] = 'Token MISMATCH';
		}

		$this->refreshGatewayValueFromSource( 'token' );
		return $checkResult;
	}

	/**
	 * Returns staged data from the adapter object, or null if a key was
	 * specified and no value exsits.
	 * @param string $val An optional specific key you want returned.
	 * @return mixed All the staged data held by the adapter, or if a key was
	 * set, the staged value for that key.
	 */
	protected function getData_Staged( $val = '' ) {
		if ( $val === '' ) {
			return $this->staged_data;
		} else {
			if ( array_key_exists( $val, $this->staged_data ) ) {
				return $this->staged_data[$val];
			} else {
				return null;
			}
		}
	}

	/**
	 * A helper function to let us stash extra data after the form has been submitted.
	 *
	 * @param array  $dataArray An associative array of data.
	 */
	public function addRequestData( $dataArray ) {
		$this->dataObj->addData( $dataArray );

		$calculated_fields = $this->dataObj->getCalculatedFields();
		$data_fields = array_keys( $dataArray );
		$data_fields = array_merge( $data_fields, $calculated_fields );

		foreach ( $data_fields as $value ) {
			$this->refreshGatewayValueFromSource( $value );
		}

		//and now check to see if you have to re-stage.
		//I'd fire off individual staging functions by value, but that's a
		//really bad idea, as multiple staged vars could be used in any staging
		//function, to calculate any other staged var.
		$changed_staged_vars = array_intersect( $this->staged_vars, $data_fields );
		if ( count( $changed_staged_vars ) ) {
			$this->stageData();
		}
	}

	/**
	 * Add data from the processor to staged_data and run any unstaging functions.
	 *
	 * @param array $dataArray An associative array of data.
	 */
	public function addResponseData( $dataArray ) {
		foreach ( $dataArray as $key => $value ) {
			$this->staged_data[$key] = $value;
		}

		$this->unstageData( $dataArray );

		// Only copy the affected values back into the normalized data.
		$newlyUnstagedData = array();
		foreach ( $dataArray as $key => $stagedValue ) {
			if ( array_key_exists( $key, $this->unstaged_data ) ) {
				$newlyUnstagedData[$key] = $this->unstaged_data[$key];
			}
		}
		$this->dataObj->addData( $newlyUnstagedData );
	}

	/**
	 * This is the ONLY getData type function anything should be using
	 * outside the adapter.
	 * Short explanation of the data population up to now:
	 *	*) When the gateway adapter is constructed, it constructs a DonationData
	 *		object.
	 *	*) On construction, the DonationData object pulls donation data from an
	 *		appropriate source, and normalizes the entire data set for storage.
	 *	*) The gateway adapter pulls normalized, html escaped data out of the
	 *		DonationData object, as the base of its own data set.
	 * @param string $val The specific key you're looking for (if any)
	 * @return mixed An array of all the raw, unstaged (but normalized and
	 * sanitized) data sent to the adapter, or if $val was set, either the
	 * specific value held for $val, or null if none exists.
	 */
	public function getData_Unstaged_Escaped( $val = '' ) {
		if ( $val === '' ) {
			return $this->unstaged_data;
		} else {
			if ( array_key_exists( $val, $this->unstaged_data ) ) {
				return $this->unstaged_data[$val];
			} else {
				return null;
			}
		}
	}

	/**
	 * This function is important.
	 * All the globals in Donation Interface should be accessed in this manner
	 * if they are meant to have a default value, but can be overridden by any
	 * of the gateways. It will check to see if a gateway-specific global
	 * exists, and if one is not set, it will pull the default from the
	 * wgDonationInterface definitions. Through this function, it is no longer
	 * necessary to define gateway-specific globals in LocalSettings unless you
	 * wish to override the default value for all gateways.
	 * If the variable exists in {prefix}AccountInfo[currentAccountName],
	 * that value will override the default settings.
	 * Caches found values in self::$globalsCache
	 *
	 * @param string $varname The global value we're looking for. It will first
	 * look for a global named for the instantiated gateway's GLOBAL_PREFIX,
	 * plus the $varname value. If that doesn't come up with anything that has
	 * been set, it will use the default value for all of donation interface,
	 * stored in $wgDonationInterface . $varname.
	 * @return mixed The configured value for that gateway if it exists. If not,
	 * the configured value for Donation Interface if it exists or not.
	 */
	static function getGlobal( $varname ) {
		//adding another layer of depth here, in case you're working with two gateways in the same request.
		//That does, in fact, ruin everything. :/
		if ( !array_key_exists( self::getGlobalPrefix(), self::$globalsCache ) ) {
			self::$globalsCache[self::getGlobalPrefix()] = array ( );
		}
		if ( !array_key_exists( $varname, self::$globalsCache[self::getGlobalPrefix()] ) ) {
			$globalname = self::getGlobalPrefix() . $varname;
			global $$globalname;
			if ( !isset( $$globalname ) ) {
				$globalname = "wgDonationInterface" . $varname;
				global $$globalname; //set or not. This is fine.
			}
			self::$globalsCache[self::getGlobalPrefix()][$varname] = $$globalname;
		}
		return self::$globalsCache[self::getGlobalPrefix()][$varname];
	}

	/**
	 * getErrorMap
	 *
	 * This will also return an error message if a $code is passed.
	 *
	 * If the error code does not exist, the default message will be returned.
	 *
	 * A default message should always exist with an index of 0.
	 *
	 * NOTE: This method will check to see if the message exists in translation
	 * and use that message instead of the default. This would override error_map.
	 *
	 * @param    string    $code    The error code to look up in the map
	 * @param    array     $options
	 * @return   array|string    Returns @see GatewayAdapter::$error_map
	 */
	public function getErrorMap( $code = null, $options = array() ) {

		if ( is_null( $code ) ) {
			return $this->error_map;
		}

		$defaults = array(
			'translate' => false,
		);
		$options = array_merge( $defaults, $options );

		$response_message = $this->getIdentifier() . '_gateway-response-' . $code;

		$translatedMessage = WmfFramework::formatMessage( $response_message );

		// FIXME: don't do this.
		// Check to see if an error message exists in translation
		if ( substr( $translatedMessage, 0, 3 ) !== '&lt;' ) {

			// Message does not exist
			$translatedMessage = '';
		}

		// If the $code does not exist, use the default message
		if ( isset( $this->error_map[ $code ] ) ) {
			$messageKey = $this->error_map[ $code ];
		} else {
			$messageKey = 'donate_interface-processing-error';
		}

		$translatedMessage = ( $options['translate'] && empty( $translatedMessage ) ) ? WmfFramework::formatMessage( $messageKey ) : $translatedMessage;

		// Check to see if we return the translated message.
		$message = ( $options['translate'] ) ? $translatedMessage : $messageKey;

		return $message;
	}

	/**
	 * getErrorMapByCodeAndTranslate
	 *
	 * This will take an error code and translate the message.
	 *
	 * @param	string	$code	The error code to look up in the map
	 *
	 * @return	string	Returns the translated message from @see GatewayAdapter::$error_map
	 */
	public function getErrorMapByCodeAndTranslate( $code ) {

		return $this->getErrorMap( $code, array( 'translate' => true, ) );
	}

	/**
	 * This function is used exclusively by the two functions that build
	 * requests to be sent directly to external payment gateway servers. Those
	 * two functions are buildRequestNameValueString, and (perhaps less
	 * obviously) buildRequestXML. As such, unless a valid current transaction
	 * has already been set, this will error out rather hard.
	 * In other words: In all likelihood, this is not the function you're
	 * looking for.
	 * @param string $gateway_field_name The GATEWAY's field name that we are
	 * hoping to populate. Probably not even remotely the way we name the same
	 * data internally.
	 * @param boolean $token This is a throwback to a road we nearly went down,
	 * with ajax and client-side token replacement. The idea was, if this was
	 * set to true, we would simply pass the fully-formed transaction structure
	 * with our tokenized var names in the spots where form values would usually
	 * go, so we could fetch the structure and have some client-side voodoo
	 * populate the transaction so we wouldn't have to touch the data at all.
	 * At this point, very likely cruft that can be removed, but as I'm not 100%
	 * on that point, I'm keeping it for now. If we do kill off this param, we
	 * should also get rid of the function buildTransactionFormat and anything
	 * that calls it.
	 * @throws LogicException
	 * @return mixed The value we want to send directly to the gateway, for the
	 * specified gateway field name.
	 */
	protected function getTransactionSpecificValue( $gateway_field_name, $token = false ) {
		if ( empty( $this->transactions ) ) {
			$msg = self::getGatewayName() . ': Transactions structure is empty! No transaction can be constructed.';
			$this->logger->critical( $msg );
			throw new LogicException( $msg );
		}
		//Ensures we are using the correct transaction structure for our various lookups.
		$transaction = $this->getCurrentTransaction();

		if ( !$transaction ){
			return null;
		}

		//If there's a hard-coded value in the transaction definition, use that.
		if ( !empty( $transaction ) ) {
			if ( array_key_exists( $transaction, $this->transactions ) && is_array( $this->transactions[$transaction] ) &&
				array_key_exists( 'values', $this->transactions[$transaction] ) &&
				array_key_exists( $gateway_field_name, $this->transactions[$transaction]['values'] ) ) {
				return $this->transactions[$transaction]['values'][$gateway_field_name];
			}
		}

		//if it's account info, use that.
		//$this->accountInfo;
		if ( array_key_exists( $gateway_field_name, $this->accountInfo ) ) {
			return $this->accountInfo[$gateway_field_name];
		}

		//If there's a value in the post data (name-translated by the var_map), use that.
		if ( array_key_exists( $gateway_field_name, $this->var_map ) ) {
			if ( $token === true ) { //we just want the field name to use, so short-circuit all that mess.
				return '@' . $this->var_map[$gateway_field_name];
			}
			$staged = $this->getData_Staged( $this->var_map[$gateway_field_name] );
			if ( !is_null( $staged ) ) {
				//if it was sent, use that.
				return $staged;
			} else {
				//return blank string
				return '';
			}
		}

		//not in the map, or hard coded. What then?
		//Complain furiously, for your code is faulty.
		$msg = self::getGatewayName() . ': Requested value ' . $gateway_field_name . ' cannot be found in the transactions structure.';
		$this->logger->critical( $msg );
		throw new LogicException( $msg );
	}

	/**
	 * Returns the current transaction request structure if it exists, otherwise
	 * returns false.
	 * Fails nicely if the current transaction is simply not set yet.
	 * @throws LogicException if the transaction is set, but no structure is defined.
	 * @return mixed current transaction's structure as an array, or false
	 */
	protected function getTransactionRequestStructure(){
		$transaction = $this->getCurrentTransaction();
		if ( !$transaction ){
			return false;
		}

		if ( empty( $this->transactions ) ||
			!array_key_exists( $transaction, $this->transactions ) ||
			!array_key_exists( 'request', $this->transactions[$transaction] ) ) {

			$msg = self::getGatewayName() . ": $transaction request structure is empty! No transaction can be constructed.";
			$this->logger->critical( $msg );
			throw new LogicException( $msg );
		}

		return $this->transactions[$transaction]['request'];
	}

	/**
	 * Builds a set of transaction data in name/value format
	 *		*)The current transaction must be set before you call this function.
	 *		*)Uses getTransactionSpecificValue to assign staged values to the
	 * fields required by the gateway. Look there for more insight into the
	 * heirarchy of all possible data sources.
	 * @return string The raw transaction in name/value format, ready to be
	 * curl'd off to the remote server.
	 */
	protected function buildRequestNameValueString() {
		// Look up the request structure for our current transaction type in the transactions array
		$structure = $this->getTransactionRequestStructure();
		if ( !is_array( $structure ) ) {
			return '';
		}

		$queryvals = array();

		//we are going to assume a flat array, because... namevalue.
		foreach ( $structure as $fieldname ) {
			$fieldvalue = $this->getTransactionSpecificValue( $fieldname );
			if ( $fieldvalue !== '' && $fieldvalue !== false ) {
				$queryvals[] = $fieldname . '=' . $fieldvalue;
			}
		}

		$ret = implode( '&', $queryvals );
		return $ret;
	}

	/**
	 * Builds a set of transaction data in XML format
	 *		*)The current transaction must be set before you call this function.
	 *		*)(eventually) uses getTransactionSpecificValue to assign staged
	 * values to the fields required by the gateway. Look there for more insight
	 * into the heirarchy of all possible data sources.
	 * @return string The raw transaction in xml format, ready to be
	 * curl'd off to the remote server.
	 */
	protected function buildRequestXML( $rootElement = 'XML', $encoding = 'UTF-8' ) {
		$this->xmlDoc = new DomDocument( '1.0', $encoding );
		$node = $this->xmlDoc->createElement( $rootElement );

		// Look up the request structure for our current transaction type in the transactions array
		$structure = $this->getTransactionRequestStructure();
		if ( !is_array( $structure ) ) {
			return '';
		}

		$this->buildTransactionNodes( $structure, $node );
		$this->xmlDoc->appendChild( $node );
		$return = $this->xmlDoc->saveXML();

		if ( $this->log_outbound ) {
			$message = "Request XML: ";
			$full_structure = $this->transactions[$this->getCurrentTransaction()]; //if we've gotten this far, this exists.
			if ( array_key_exists( 'never_log', $full_structure ) ) { //Danger Zone!
				$message = "Cleaned $message";
				//keep these totally separate. Do not want to risk sensitive information (like cvv) making it anywhere near the log.
				$this->xmlDoc = new DomDocument( '1.0' );
				$log_node = $this->xmlDoc->createElement( $rootElement );
				//remove all never_log nodes from the structure
				$log_structure = $this->cleanTransactionStructureForLogs( $structure, $full_structure['never_log'] );
				$this->buildTransactionNodes( $log_structure, $log_node );
				$this->xmlDoc->appendChild( $log_node );
				$logme = $this->xmlDoc->saveXML();
			} else {
				//...safe zone.
				$logme = $return;
			}
			$this->logger->info( $message . $logme );
		}


		return $return;
	}

	/**
	 * buildRequestXML helper function.
	 * Builds the XML transaction by recursively crawling the transaction
	 * structure and adding populated nodes by reference.
	 * @param array $structure Current transaction's more leafward structure,
	 * from the point of view of the current XML node.
	 * @param xmlNode $node The current XML node.
	 * @param bool $js More likely cruft relating back to buildTransactionFormat
	 */
	protected function buildTransactionNodes( $structure, &$node, $js = false ) {

		if ( !is_array( $structure ) ) { //this is a weird case that shouldn't ever happen. I'm just being... thorough. But, yeah: It's like... the base-1 case.
			$this->appendNodeIfValue( $structure, $node, $js );
		} else {
			foreach ( $structure as $key => $value ) {
				if ( !is_array( $value ) ) {
					//do not use $key. $key is meaningless in this case.
					$this->appendNodeIfValue( $value, $node, $js );
				} else {
					$keynode = $this->xmlDoc->createElement( $key );
					$this->buildTransactionNodes( $value, $keynode, $js );
					$node->appendChild( $keynode );
				}
			}
		}
		//not actually returning anything. It's all side-effects. Because I suck like that.
	}

	/**
	 * Recursively sink through a transaction structure array to remove all
	 * nodes that we can't have showing up in the server logs.
	 * Mostly for CVV: If we log those, we are all fired.
	 * @param array $structure The transaction structure that we want to clean.
	 * @param array $never_log An array of values we should never log. These values should be the gateway's transaciton nodes, rather than our normal values.
	 * @return array $structure stripped of all references to the values in $never_log
	 */
	protected function cleanTransactionStructureForLogs( $structure, $never_log ) {
		foreach ( $structure as $node => $value ) {
			if ( is_array( $value ) ) {
				$structure[$node] = $this->cleanTransactionStructureForLogs( $value, $never_log );
			} else {
				if ( in_array( $value, $never_log ) ) {
					unset( $structure[$node] );
				}
			}
		}
		return $structure;
	}

	/**
	 * appendNodeIfValue is a helper function for buildTransactionNodes, which
	 * is used by buildRequestXML to construct an XML transaction.
	 * This function will append an XML node to the transaction being built via
	 * the passed-in parent node, only if the current node would have a
	 * non-empty value.
	 * @param string $value The GATEWAY's field name for the current node.
	 * @param string $node The parent node this node will be contained in, if it
	 *  is determined to have a non-empty value.
	 * @param bool $js Probably cruft at this point. This is connected to the
	 * function buildTransactionFormat.
	 */
	protected function appendNodeIfValue( $value, &$node, $js = false ) {
		$nodevalue = $this->getTransactionSpecificValue( $value, $js );
		if ( $nodevalue !== '' && $nodevalue !== false ) {
			$temp = $this->xmlDoc->createElement( $value, $nodevalue );
			$node->appendChild( $temp );
		}
	}

	/**
	 * Performs a transaction through the gateway. Optionally may reattempt the transaction if
	 * a recoverable gateway error occurred.
	 *
	 * This function provides all functionality to the external world to communicate with a
	 * properly constructed gateway and handle all the return data in an appropriate manner.
	 * -- Appropriateness is determined by the requested $transaction structure and definition/
	 *
	 * @param string  | $transaction    The specific transaction type, like 'INSERT_ORDERWITHPAYMENT',
	 *  that maps to a first-level key in the $transactions array.
	 *
	 * @return PaymentTransactionResponse
	 */
	public function do_transaction( $transaction ) {
		$this->session_addDonorData();
		if ( !$this->validatedOK() ){
			//If the data didn't validate okay, prevent all data transmissions.
			$return = new PaymentTransactionResponse();
			$return->setCommunicationStatus( false );
			$return->setMessage( 'Failed data validation' );
			foreach( $this->getAllErrors() as $code => $error ) {
				$return->addError( $code, array( 'message' => $error, 'logLevel' => LogLevel::INFO, 'debugInfo' => '' ) );
			}
			// TODO: should we set $this->transaction_response ?
			$this->logger->info( "Failed Validation. Aborting $transaction " . print_r( $this->getValidationErrors(), true ) );
			return $return;
		}

		$retryCount = 0;
		$loopCount = $this->getGlobal( 'RetryLoopCount' );

		do {
			$retryVars = null;
			$retval = $this->do_transaction_internal( $transaction, $retryVars );

			if ( !empty( $retryVars ) ) {
				// TODO: Add more intelligence here. Right now we just assume it's the order_id
				// and that it is totally OK to just reset it and reroll.

				$this->logger->info( "Repeating transaction on request for vars: " . implode( ',', $retryVars ) );

				// Force regen of the order_id
				$this->regenerateOrderID();

				// Pull anything changed from dataObj
				$this->unstaged_data = $this->dataObj->getDataEscaped();
				$this->staged_data = $this->unstaged_data;
				$this->stageData();
			}

		} while ( ( !empty( $retryVars ) ) && ( ++$retryCount < $loopCount ) );

		if ( $retryCount >= $loopCount ) {
			$this->logger->error( "Transaction canceled after $retryCount retries." );
		}

		return $retval;
	}

	/**
	 * Called from do_transaction() in order to be able to deal with transactions that had
	 * recoverable errors but that do require the entire transaction to be repeated.
	 *
	 * This function has the following extension hooks:
	 *  * pre_process_<strtolower($transaction)>
	 *    Called before the transaction is processed; intended to call setValidationAction()
	 *    if the transaction should not be performed. Anti-fraud can be performed in this
	 *    hook by calling $this->runAntifraudHooks().
	 *
	 *  * MediaWiki hook GatewayHandoff
	 *    Called if the gateway tranaction type is 'redirect'
	 *
	 *  * post_process_<strtolower($transaction)>
	 *
	 * @param string    $transaction Name of the transaction being performed
	 * @param &string() $retryVars Reference to an array of variables that caused the
	 *                  transaction to fail.
	 *
	 * @return PaymentTransactionResponse
	 * @throws UnexpectedValueException
	 */
	final private function do_transaction_internal( $transaction, &$retryVars = null ) {
		$this->debugarray[] = __FUNCTION__ . " is doing a $transaction.";

		//reset, in case this isn't our first time.
		$this->transaction_response = new PaymentTransactionResponse();
		$this->final_status = false;
		$this->setValidationAction( 'process', true );
		$errCode = null;

		/* --- Build the transaction string for cURL --- */
		try {
			$this->setCurrentTransaction( $transaction );

			$this->executeIfFunctionExists( 'pre_process_' . $transaction );
			if ( $this->getValidationAction() != 'process' ) {
				$this->logger->info( "Failed pre-process checks for transaction type $transaction." );
				$this->transaction_response->setCommunicationStatus( false );
				$this->transaction_response->setMessage( $this->getErrorMapByCodeAndTranslate( 'internal-0000' ) );
				$this->transaction_response->setErrors( array(
					'internal-0000' => array(
						'debugInfo' => "Failed pre-process checks for transaction type $transaction.",
						'message' => $this->getErrorMapByCodeAndTranslate( 'internal-0000' ),
						'logLevel' => LogLevel::INFO
					)
				) );
				return $this->transaction_response;
			}

			if ( !$this->isBatchProcessor() ) {
				//TODO: Maybe move this to the pre_process functions?
				$this->dataObj->saveContributionTrackingData();
			}

			$commType = $this->getCommunicationType();
			if ( $commType === 'redirect' ) {
				WmfFramework::runHooks( 'GatewayHandoff', array ( $this ) );

				//in the event that we have a redirect transaction that never displays the form,
				//save this most recent one before we leave.
				$this->session_pushRapidHTMLForm( $this->getData_Unstaged_Escaped( 'ffname' ) );

				$this->transaction_response->setCommunicationStatus( true );
				$this->transaction_response->setRedirect( $this->url );
				return $this->transaction_response;

			} elseif ( $commType === 'xml' ) {
				$this->getStopwatch( "buildRequestXML", true ); // begin profiling
				$curlme = $this->buildRequestXML(); // build the XML
				$this->saveCommunicationStats( "buildRequestXML", $transaction ); // save profiling data

			} elseif ( $commType === 'namevalue' ) {
				$this->getStopwatch( "buildRequestNameValueString", true ); // begin profiling
				$curlme = $this->buildRequestNameValueString(); // build the name/value pairs
				$this->saveCommunicationStats( "buildRequestNameValueString", $transaction ); // save profiling data

			} else {
				throw new UnexpectedValueException( "Communication type of '{$commType}' unknown" );
			}
		} catch ( Exception $e ) {
			$this->logger->critical( 'Malformed gateway definition. Cannot continue: Aborting.\n' . $e->getMessage() );

			$this->transaction_response->setCommunicationStatus( false );
			$this->transaction_response->setMessage( $this->getErrorMapByCodeAndTranslate( 'internal-0001' ) );
			$this->transaction_response->setErrors( array(
				'internal-0001' => array(
					'debugInfo' => 'Malformed gateway definition. Cannot continue: Aborting.\n' . $e->getMessage(),
					'message' => $this->getErrorMapByCodeAndTranslate( 'internal-0001' ),
					'logLevel' => LogLevel::CRITICAL
				)
			) );

			return $this->transaction_response;
		}

		/* --- Do the cURL request --- */
		$this->getStopwatch( __FUNCTION__, true );
		$txn_ok = $this->curl_transaction( $curlme );
		if ( $txn_ok === true ) { //We have something to slice and dice.
			$this->logger->info( "RETURNED FROM CURL:" . print_r( $this->transaction_response->getRawResponse(), true ) );

			// Decode the response according to $this->getResponseType
			$formatted = $this->getFormattedResponse( $this->transaction_response->getRawResponse() );

			// Process the formatted response. This will then drive the result action
			try{
				$this->processResponse( $formatted );
			} catch ( ResponseProcessingException $ex ) {
				$errCode = $ex->getErrorCode();
				$retryVars = $ex->getRetryVars();
				$this->transaction_response->addError( $errCode, array(
					'message' => $this->getErrorMapByCodeAndTranslate( 'internal-0001' ),
					'debugInfo' => $ex->getMessage(),
					'logLevel' => LogLevel::ERROR
				) );
			}

		} elseif ( $txn_ok === false ) { //nothing to process, so we have to build it manually
			$logMessage = 'Transaction Communication failed' . print_r( $this->transaction_response, true );
			$this->logger->error( $logMessage );

			$this->transaction_response->setCommunicationStatus( false );
			$this->transaction_response->setMessage( $this->getErrorMapByCodeAndTranslate( 'internal-0002' ) );
			$this->transaction_response->setErrors( array(
				'internal-0002' => array(
					'debugInfo' => $logMessage,
					'message' => $this->getErrorMapByCodeAndTranslate( 'internal-0002' ),
					'logLevel' => LogLevel::ERROR
				)
			) );
		}

		// Log out how much time it took for the cURL request
		$this->saveCommunicationStats( __FUNCTION__, $transaction );

		if ( !empty( $retryVars ) ) {
			$this->logger->critical( "$transaction Communication failed (errcode $errCode), will reattempt!" );

			// Set this by key so that the result object still has all the cURL data
			$this->transaction_response->setCommunicationStatus( false );
			$this->transaction_response->setMessage( $this->getErrorMapByCodeAndTranslate( $errCode ) );
			$this->transaction_response->setErrors( array(
				$errCode => array(
					'debugInfo' => "$transaction Communication failed (errcode $errCode), will reattempt!",
					'message' => $this->getErrorMapByCodeAndTranslate( $errCode ),
					'logLevel' => LogLevel::CRITICAL
				)
			) );
		}

		//if we have set errors by this point, the transaction is not okay
		$errors = $this->getTransactionErrors();
		if ( !empty( $errors ) ) {
			$txn_ok = false;
		}
		//If we have any special post-process instructions for this
		//transaction, do 'em.
		//NOTE: If you want your transaction to fire off the post-process
		//hooks, you need to run $this->runPostProcessHooks in a function
		//called
		//	'post_process' . strtolower($transaction)
		//in the appropriate gateway object.
		if ( $txn_ok && empty( $retryVars ) ) {
			$this->executeIfFunctionExists( 'post_process_' . $transaction );
			if ( $this->getValidationAction() != 'process' ) {
				$this->logger->info( "Failed post-process checks for transaction type $transaction." );
				$this->transaction_response->setCommunicationStatus( false );
				$this->transaction_response->setMessage( $this->getErrorMapByCodeAndTranslate( 'internal-0000' ) );
				$this->transaction_response->setErrors( array(
					'internal-0000' => array(
						'debugInfo' => "Failed post-process checks for transaction type $transaction.",
						'message' => $this->getErrorMapByCodeAndTranslate( 'internal-0000' ),
						'logLevel' => LogLevel::INFO
					)
				) );
				return $this->transaction_response;
			}
		}

		// log that the transaction is essentially complete
		$this->logger->info( 'Transaction complete.' );

		$this->debugarray[] = 'numAttempt = ' . self::session_getData( 'numAttempt' );

		return $this->transaction_response;
	}

	function getCurlBaseOpts() {
		//I chose to return this as a function so it's easy to override.
		//TODO: probably this for all the junk I currently have stashed in the constructor.
		//...maybe.
		$path = $this->transaction_option( 'path' );
		if ( !$path ) {
			$path = '';
		}
		$opts = array(
			CURLOPT_URL => $this->url . $path,
			CURLOPT_USERAGENT => WmfFramework::getUserAgent(),
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => self::getGlobal( 'Timeout' ),
			CURLOPT_FOLLOWLOCATION => 0,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_POST => 1,
		);

		return $opts;
	}

	function getCurlBaseHeaders() {
		$content_type = 'application/x-www-form-urlencoded';
		if ( $this->getCommunicationType() === 'xml' ) {
			$content_type = 'text/xml';
		}
		$headers = array(
			'Content-Type: ' . $content_type . '; charset=utf-8',
			'X-VPS-Client-Timeout: 45',
			'X-VPS-Request-ID:' . $this->getData_Staged( 'order_id' ),
		);
		return $headers;
	}

	/**
	 * Sets the transaction you are about to send to the payment gateway. This
	 * will throw an exception if you try to set it to something that has no
	 * transaction definition.
	 * @param type $transaction_name This is a specific transaction type like
	 * 'INSERT_ORDERWITHPAYMENT' (if you're GlobalCollect) that maps to a
	 * first-level key in the $transactions array.
	 * @throws UnexpectedValueException
	 */
	public function setCurrentTransaction( $transaction_name ){
		if ( empty( $this->transactions ) || !is_array( $this->transactions ) || !array_key_exists( $transaction_name, $this->transactions ) ) {
			$msg = self::getGatewayName() . ': Transaction Name "' . $transaction_name . '" undefined for this gateway.';
			$this->logger->alert( $msg );
			throw new UnexpectedValueException( $msg );
		} else {
			$this->current_transaction = $transaction_name;
		}

		// XXX WIP
		$override_options = array(
			'url',
		);
		foreach ( $override_options as $key ) {
			$override_val = $this->transaction_option( $key );
			// XXX this hack should probably be pushed down to something
			// like "setTransactionOptions" so that we can override with
			// a NULL value when we need to
			if ( $override_val !== NULL ) {
				$this->$key = $override_val;
			}
		}

	}

	/**
	 * Gets the currently set transaction name. This value should only ever be
	 * set with setCurrentTransaction: A function that ensures the current
	 * transaction maps to a first-level key that is known to exist in the
	 * $transactions array, defined in the child gateway.
	 * @return mixed The name of the properly set transaction, or false if none
	 * has been set.
	 */
	public function getCurrentTransaction(){
		if ( is_null( $this->current_transaction ) ) {
			return false;
		} else {
			return $this->current_transaction;
		}
	}

	/**
	 * Get the payment method
	 *
	 * @return	string
	 */
	public function getPaymentMethod() {
		//FIXME: this should return the final calculated method
		return $this->getData_Unstaged_Escaped('payment_method');
	}

	/**
	 * Define payment methods
	 *
	 * Not all payment methods are available within an adapter
	 *
	 * @return	array	Returns the available payment methods for the specific adapter
	 */
	public function getPaymentMethods() {
		return $this->payment_methods;
	}

	/**
	 * Get the payment submethod
	 *
	 * @return	string
	 */
	public function getPaymentSubmethod() {

		return $this->getData_Unstaged_Escaped('payment_submethod');
	}

	/**
	 * Define payment methods
	 *
	 * @todo
	 * - this is not implemented in all adapters yet
	 *
	 * Not all payment submethods are available within an adapter
	 *
	 * @return	array	Returns the available payment submethods for the specific adapter
	 */
	public function getPaymentSubmethods() {
		return $this->payment_submethods;
	}

	/**
	 * Sends a curl request to the gateway server, and gets a response.
	 * Saves that response to the transaction_response's rawResponse;
	 * @param string $data the raw data we want to curl up to a server somewhere.
	 * Should have been constructed with either buildRequestNameValueString, or
	 * buildRequestXML.
	 * @return boolean true if the communication was successful and there is a
	 * parseable response, false if there was a fundamental communication
	 * problem. (timeout, bad URL, etc.)
	 */
	protected function curl_transaction( $data ) {
		$this->getStopwatch( __FUNCTION__, true );

		// Basic variable init
		$retval = false;    // By default return that we failed

		$gatewayName = self::getGatewayName();
		$email = $this->getData_Unstaged_Escaped( 'email' );

		/**
		 * This log line is pretty important. Usually when a donor contacts us
		 * saying that they have experienced problems donating, the first thing
		 * we have to do is associate a gateway transaction ID and ctid with an
		 * email address. If the cURL function fails, we lose the ability to do
		 * that association outside of this log line.
		 */
		$this->logger->info( "Initiating cURL for donor $email" );

		// Initialize cURL and construct operation (also run hook)
		$ch = curl_init();

		$hookResult = WmfFramework::runHooks( 'DonationInterfaceCurlInit', array( &$this ) );
		if ( $hookResult == false ) {
			$this->logger->info( 'cURL transaction aborted on hook DonationInterfaceCurlInit' );
			$this->setValidationAction('reject');
			return false;
		}

		// assign header data necessary for the curl_setopt() function
		$headers = $this->getCurlBaseHeaders();
		$headers[] = 'Content-Length: ' . strlen( $data );

		$curl_opts = $this->getCurlBaseOpts();
		$curl_opts[CURLOPT_HTTPHEADER] = $headers;
		$curl_opts[CURLOPT_POSTFIELDS] = $data;

		curl_setopt_array( $ch, $curl_opts );

		// As suggested in the PayPal developer forum sample code, try more than once to get a
		// response in case there is a general network issue
		$continue = true;
		$tries = 0;
		$curl_response = false;
		$loopCount = $this->getGlobal( 'RetryLoopCount' );

		do {
			$this->logger->info( "Preparing to send {$this->getCurrentTransaction()} transaction to $gatewayName" );

			// Execute the cURL operation
			$curl_response = $this->curl_exec( $ch );

			if ( $curl_response !== false ) {
				// The cURL operation was at least successful, what happened in it?

				$headers = $this->curl_getinfo( $ch );
				$httpCode = $headers['http_code'];

				switch ( $httpCode ) {
					case 200:   // Everything is AWESOME
						$continue = false;

						$this->logger->debug( "Successful transaction to $gatewayName" );
						$this->transaction_response->setRawResponse( $curl_response );

						$retval = true;
						break;

					case 400:   // Oh noes! Bad request.. BAD CODE, BAD BAD CODE!
						$continue = false;

						$this->logger->error( "$gatewayName returned (400) BAD REQUEST: $curl_response" );

						// Even though there was an error, set the results. Amazon at least gives
						// us useful XML return
						$this->transaction_response->setRawResponse( $curl_response );

						$retval = true;
						break;

					case 403:   // Hmm, forbidden? Maybe if we ask it nicely again...
						$continue = true;
						$this->logger->alert( "$gatewayName returned (403) FORBIDDEN: $curl_response" );
						break;

					default:    // No clue what happened... break out and log it
						$continue = false;
						$this->logger->error( "$gatewayName failed remotely and returned ($httpCode): $curl_response" );
						break;
				}
			} else {
				// Well the cURL transaction failed for some reason or another. Try again!
				$continue = true;

				$errno = $this->curl_errno( $ch );
				$err = curl_error( $ch );
				$this->logger->alert( "cURL transaction  to $gatewayName failed: ($errno) $err" );
			}
			$tries++;
			if ( $tries >= $loopCount ) {
				$continue = false;
			}
			if ( $continue ) {
				// If we're going to try again, log timing for this particular curl attempt and reset
				$this->saveCommunicationStats( __FUNCTION__, $this->getCurrentTransaction(), "cURL problems" );
				$this->getStopwatch( __FUNCTION__, true );
			}
		} while ( $continue ); // End while cURL transaction hasn't returned something useful

		// Clean up and return
		curl_close( $ch );
		$log_results = array(
			'result' => $curl_response,
			'headers' => $headers,
		);
		$this->saveCommunicationStats( __FUNCTION__, $this->getCurrentTransaction(), "Response: " . print_r( $log_results, true ) );

		return $retval;
	}

	/**
	 * Wrapper for the real curl_exec so we can override with magic for unit tests.
	 * @param resource $ch curl handle (returned from curl_init)
	 * @return mixed True or the result on success (depends if
	 * CURLOPT_RETURNTRANSFER is set or not). False on total failure.
	 */
	protected function curl_exec( $ch ) {
		return curl_exec( $ch );
	}

	/**
	 * Wrapper for the real curl_getinfo so we can override with magic for unit tests.
	 * @param resource $ch curl handle (returned from curl_init)
	 * @return mixed an array, string, or false on total failure.
	 */
	protected function curl_getinfo( $ch ) {
		return curl_getinfo( $ch );
	}

	/**
	 * Wrapper for the real curl_errno so we can override with magic for unit tests.
	 * @param resource $ch curl handle (returned from curl_init)
	 * @return int the error number or 0 if none occurred
	 */
	protected function curl_errno( $ch ) {
		return curl_errno( $ch );
	}

	/**
	 * Check the response for general sanity - e.g. correct data format, keys exists
	 * @return boolean true if response looks sane
	 */
	protected function parseResponseCommunicationStatus( $response ) {
		return true;
	}

	/**
	 * Parse the response to get the errors in a format we can log and otherwise deal with.
	 * @return array a key/value array of codes (if they exist) and messages.
	 */
	protected function parseResponseErrors( $response ) {
		return array();
	}

	/**
	 * Harvest the data we need back from the gateway.
	 * @return array a key/value array
	 */
	protected function parseResponseData( $response ) {
		return array();
	}

	/**
	 * Take the entire response string, and strip everything we don't care
	 * about.  For instance: If it's XML, we only want correctly-formatted XML.
	 * Headers must be killed off.
	 * @param string $rawResponse hot off the curl
	 * @return string|DomDocument|array depending on $this->getResponseType
	 * @throws InvalidArgumentException
	 * @throws LogicException
	 */
	function getFormattedResponse( $rawResponse ) {
		$type = $this->getResponseType();
		if ( $type === 'xml' ) {
			$xmlString = $this->stripXMLResponseHeaders( $rawResponse );
			$displayXML = $this->formatXmlString( $xmlString );
			$realXML = new DomDocument( '1.0' );
			//DO NOT alter the line below unless you are prepared to also alter the GC audit scripts.
			//...and everything that references "Raw XML Response"
			//@TODO: All three of those things.
			$this->logger->info( "Raw XML Response:\n" . $displayXML ); //I am apparently a huge fibber.
			$realXML->loadXML( trim( $xmlString ) );
			return $realXML;
		}
		// For anything else, delete all the headers and the blank line after
		$noHeaders = preg_replace( '/^.*?\n\r?\n/ms', '', $rawResponse, 1 );
		$this->logger->info( "Raw Response:" . $noHeaders );
		if ( $type === 'json' ) {
			return json_decode( $noHeaders, true );
		}
		if ( $type === 'delimited' ) {
			$delimiter = $this->transaction_option( 'response_delimiter' );
			$keys = $this->transaction_option( 'response_keys' );
			if ( !$delimiter || !$keys ) {
				throw new LogicException( 'Delimited transactions must define both response_delimiter and response_keys options' );
			}
			$values = explode( $delimiter, trim( $noHeaders ) );
			$combined = array_combine( $keys, $values );
			if ( $combined === FALSE ) {
				throw new InvalidArgumentException( 'Wrong number of values found in delimited response.');
			}
			return $combined;
		}
		return $noHeaders;
	}

	function stripXMLResponseHeaders( $rawResponse ) {
		$xmlStart = strpos( $rawResponse, '<?xml' );
		if ( $xmlStart === false ) {
			//I totally saw this happen one time. No XML, just <RESPONSE>...
			//...Weaken to almost no error checking.  Buckle up!
			$xmlStart = strpos( $rawResponse, '<' );
		}
		if ( $xmlStart === false ) { //Still false. Your Head Asplode.
			$this->logger->error( "Completely Mangled Response:\n" . $rawResponse );
			return false;
		}
		$justXML = substr( $rawResponse, $xmlStart );
		return $justXML;
	}

	//To avoid reinventing the wheel: taken from http://recursive-design.com/blog/2007/04/05/format-xml-with-php/
	function formatXmlString( $xml ) {
		// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
		$xml = preg_replace( '/(>)(<)(\/*)/', "$1\n$2$3", $xml );

		// now indent the tags
		$token = strtok( $xml, "\n" );
		$result = ''; // holds formatted version as it is built
		$pad = 0; // initial indent
		$matches = array(); // returns from preg_matches()
		// scan each line and adjust indent based on opening/closing tags
		while ( $token !== false ) :

			// test for the various tag states
			// 1. open and closing tags on same line - no change
			if ( preg_match( '/.+<\/\w[^>]*>$/', $token, $matches ) ) :
				$indent = 0;
			// 2. closing tag - outdent now
			elseif ( preg_match( '/^<\/\w/', $token, $matches ) ) :
				$pad--;
			// 3. opening tag - don't pad this one, only subsequent tags
			elseif ( preg_match( '/^<\w[^>]*[^\/]>.*$/', $token, $matches ) ) :
				$indent = 1;
			// 4. no indentation needed
			else :
				$indent = 0;
			endif;

			// pad the line with the required number of leading spaces
			$line = str_pad( $token, strlen( $token ) + $pad, ' ', STR_PAD_LEFT );
			$result .= $line . "\n"; // add to the cumulative result, with linefeed
			$token = strtok( "\n" ); // get the next token
			$pad += $indent; // update the pad size for subsequent lines
		endwhile;

		return $result;
	}

	static function getGatewayName() {
		$c = get_called_class();
		return $c::GATEWAY_NAME;
	}

	static function getGlobalPrefix() {
		$c = get_called_class();
		return $c::GLOBAL_PREFIX;
	}

	static function getIdentifier() {
		$c = get_called_class();
		return $c::IDENTIFIER;
	}

	static function getLogIdentifier() {
		return self::getIdentifier() . '_gateway';
	}
	/**
	 * getStopwatch keeps track of how long things take, for logging,
	 * output, determining if we should loop on some method again... whatever.
	 * @staticvar array $start The microtime at which a stopwatch was started.
	 * @param string $string Some identifier for each stopwatch value we want to
	 * keep. Each unique $string passed in will get its own value in $start.
	 * @param bool $reset If this is set to true, it will reset any $start value
	 * recorded for the $string identifier.
	 * @return numeric The difference in microtime (rounded to 4 decimal places)
	 * between the $start value, and now.
	 */
	public function getStopwatch( $string, $reset = false ) {
		static $start = array();
		$now = microtime( true );

		if ( empty( $start ) || !array_key_exists( $string, $start ) || $reset === true ) {
			$start[$string] = $now;
		}
		$clock = round( $now - $start[$string], 4 );
		$this->logger->info( "Clock at $string: $clock ($now)" );
		return $clock;
	}

	/**
	 * @param string $function This is the function name that identifies the
	 * stopwatch that should have already been started with the getStopwatch
	 * function.
	 * @param string $additional Additional information about the thing we're
	 * currently timing. Meant to be easily searchable.
	 * @param string $vars Intended to be particular values of any variables
	 * that might be of interest.
	 */
	public function saveCommunicationStats( $function = '', $additional = '', $vars = '' ) {
		static $saveStats = null;
		static $saveDB = null;

		if ( $saveStats === null ){
			$saveStats = self::getGlobal( 'SaveCommStats' );
		}

		if ( !$saveStats ){
			return;
		}

		if ( $saveDB === null && !$this->isBatchProcessor() ) {
			$db = ContributionTrackingProcessor::contributionTrackingConnection();
			if ( $db->tableExists( 'communication_stats' ) ) {
				$saveDB = true;
			} else {
				$saveDB = false;
			}
		}

		$params = array(
			'contribution_id' => $this->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'duration' => $this->getStopwatch( $function ),
			'gateway' => self::getGatewayName(),
			'function' => $function,
			'vars' => $vars,
			'additional' => $additional,
		);

		if ( $saveDB ){
			$db = ContributionTrackingProcessor::contributionTrackingConnection();
			$params['ts'] = $db->timestamp();
			$db->insert( 'communication_stats', $params );
		} else {
			//save to syslog. But which syslog?
			$msg = '';
			foreach ($params as $key=>$val){
				$msg .= "$key:$val - ";
			}
			$this->commstats_logger->info( $msg );
		}
	}

	function xmlChildrenToArray( $xml, $nodename ) {
		$data = array();
		foreach ( $xml->getElementsByTagName( $nodename ) as $node ) {
			foreach ( $node->childNodes as $childnode ) {
				if ( trim( $childnode->nodeValue ) != '' ) {
					$data[$childnode->nodeName] = $childnode->nodeValue;
				}
			}
		}
		return $data;
	}

	/**
	 * addCodeRange is used to define ranges of response codes for major
	 * gateway transactions, that let us know what status bucket to sort
	 * them into.
	 * DO NOT DEFINE OVERLAPPING RANGES!
	 * TODO: Make sure it won't let you add overlapping ranges. That would
	 * probably necessitate the sort moving to here, too.
	 * @param string $transaction The transaction these codes map to.
	 * @param string $key The (incoming) field name containing the numeric codes
	 * we're defining here.
	 * @param string $action One of the constants defined in @see FinalStatus.
	 * @param int $lower The integer value of the lower-bound in this code range.
	 * @param int $upper Optional: The integer value of the upper-bound in the
	 * code range. If omitted, it will make a range of one value: The lower bound.
	 * @throws UnexpectedValueException
	 * @return void
	 */
	protected function addCodeRange( $transaction, $key, $action, $lower, $upper = null ) {
		if ( $upper === null ) {
			$this->return_value_map[$transaction][$key][$lower] = $action;
		} else {
			$this->return_value_map[$transaction][$key][$upper] = array( 'action' => $action, 'lower' => $lower );
		}
	}

	/**
	 * findCodeAction
	 *
	 * @param	string			$transaction
	 * @param	string			$key			The key to lookup in the transaction such as STATUSID
	 * @param	integer|string	$code			This gets converted to an integer if the values is numeric.
	 * FIXME: We should be pulling $code out of the current transaction fields, internally.
	 * FIXME: Rename to reflect that these are Final Status values, not validation actions
	 * @return	null|string	Returns the code action if a valid code is supplied. Otherwise, the return is null.
	 */
	public function findCodeAction( $transaction, $key, $code ) {

		$this->getStopwatch( __FUNCTION__, true );

		// Do not allow anything that is not numeric
		if ( !is_numeric( $code ) ) {
			return null;
		}

		// Cast the code as an integer
		settype( $code, 'integer');

		// Check to see if the transaction is defined
		if ( !array_key_exists( $transaction, $this->return_value_map ) ) {
			return null;
		}

		// Verify the key exists within the transaction
		if ( !array_key_exists( $key, $this->return_value_map[ $transaction ] ) || !is_array( $this->return_value_map[ $transaction ][ $key ] ) ) {
			return null;
		}

		//sort the array so we can do this quickly.
		ksort( $this->return_value_map[ $transaction ][ $key ], SORT_NUMERIC );

		$ranges = $this->return_value_map[ $transaction ][ $key ];
		//so, you have a code, which is a number. You also have a numerically sorted array.
		//loop through until you find an upper >= your code.
		//make sure it's in the range, and return the action.
		foreach ( $ranges as $upper => $val ) {
			if ( $upper >= $code ) { //you've arrived. It's either here or it's nowhere.
				if ( is_array( $val ) ) {
					if ( $val['lower'] <= $code ) {
						return $val['action'];
					} else {
						return null;
					}
				} else {
					if ( $upper === $code ) {
						return $val;
					} else {
						return null;
					}
				}
			}
		}
		//if we walk straight off the end...
		return null;
	}

	/**
	 * Saves a stomp frame to the configured server and queue, based on the
	 * outcome of our current transaction.
	 * The big tricky thing here, is that we DO NOT SET a FinalStatus,
	 * unless we have just learned what happened to a donation in progress,
	 * through performing the current transaction.
	 * To put it another way, getFinalStatus should always return
	 * false, unless it's new data about a new transaction. In that case, the
	 * outcome will be assigned and the proper queue selected.
	 *
	 * Probably called in runPostProcessHooks(), which is itself most likely to
	 * be called through executeFunctionIfExists, later on in do_transaction.
	 */
	protected function doStompTransaction() {
		$status = $this->getFinalStatus();
		switch ( $status ) {
			case FinalStatus::COMPLETE:
				$this->pushMessage( 'complete' );
				break;

			case FinalStatus::PENDING:
			case FinalStatus::PENDING_POKE:
				// FIXME: I don't understand what the pending queue does.
				$this->pushMessage( 'pending' );
				break;

			default:
				// No action
				$this->logger->info( "Not sending queue message for status {$status}." );
		}
	}

	/**
	 * Formats an array in preparation for dispatch to a STOMP queue
	 *
	 * @return array Pass this return array to STOMP :)
	 *
	 * TODO: Stop saying "STOMP".
	 */
	protected function getStompTransaction() {
		$transaction = array(
			'gateway_txn_id' => $this->getTransactionGatewayTxnID(),
			'response' => $this->getTransactionMessage(),
			// Can this be deprecated?
			'correlation-id' => $this->getCorrelationID(),
			'php-message-class' => 'SmashPig\CrmLink\Messages\DonationInterfaceMessage',
		);

		// Add the rest of the relevant data
		$stomp_data = array_intersect_key(
			$this->getData_Unstaged_Escaped(),
			array_flip( $this->dataObj->getStompMessageFields() )
		);

		// The order here is important, values in $transaction are considered more definitive
		// in case the transaction already had keys with those values
		$transaction = array_merge( $stomp_data, $transaction );

		// FIXME: Note that we're not using any existing date or ts fields.  Why is that?
		$transaction['date'] = time();

		return $transaction;
	}

	/**
	 * For making freeform stomp messages.
	 * As these are all non-critical, we don't need to be as strict as we have been with the other stuff.
	 * But, we've got to have some standards.
	 * @param array $transaction The fields that we are interested in sending.
	 * @return array The fields that will actually be sent. So, $transaction ++ some other things we think we're likely to always need.
	 */
	public function makeFreeformStompTransaction( $transaction ) {
		if ( !array_key_exists( 'php-message-class', $transaction ) ) {
			$this->logger->warning( "Trying to send a freeform STOMP message with no class defined. Bad programmer." );
			$transaction['php-message-class'] = 'undefined-loser-message';
		}

		// Mark as freeform so we avoid normalization.
		$transaction['freeform'] = true;

		//bascially, add all the stuff we have come to take for granted, because syslog.
		$transaction['gateway_txn_id'] = $this->getTransactionGatewayTxnID();
		$transaction['correlation-id'] = $this->getCorrelationID();
		$transaction['date'] = ( int ) time(); //I know this looks odd. Just trust me here.
		$transaction['server'] = WmfFramework::getHostname();

		$these_too = array (
			'gateway',
			'contribution_tracking_id',
			'order_id',
			'payment_method', //the stomp sender gets mad if we don't have this. @TODO: Stop being lazy someday.
		);
		foreach ( $these_too as $field ) {
			$transaction[$field] = $this->getData_Unstaged_Escaped( $field );
		}

		return $transaction;
	}

	protected function getCorrelationID(){
		return $this->getIdentifier() . '-' . $this->getData_Unstaged_Escaped('order_id');
	}

	/**
	 * Executes the specified function in $this, if one exists.
	 * NOTE: THIS WILL LCASE YOUR FUNCTION_NAME.
	 * ...I like to keep the voodoo functions tidy.
	 * @param string $function_name The name of the function you're hoping to
	 * execute.
	 * @param mixed $parameter That's right: For now you only get one.
	 * @return bool True if a function was found and executed.
	 */
	function executeIfFunctionExists( $function_name, $parameter = null ) {
		$function_name = strtolower( $function_name ); //Because, that's why.
		if ( method_exists( $this, $function_name ) ) {
			$this->{$function_name}( $parameter );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Run any staging functions provided by the adapter
	 */
	protected function stageData() {
		// Copy data, the default is to not change the values.
		//reset from our normalized unstaged data so we never double-stage
		$this->staged_data = $this->unstaged_data;

		// This allows transactions to each stage different data.
		$this->defineStagedVars();

		// Always stage email address first, to set default if missing
		array_unshift( $this->staged_vars, 'email' );

		foreach ( $this->staged_vars as $field ) {
			$function_name = 'stage_' . $field;
			$this->executeIfFunctionExists( $function_name );
		}

		// Format the staged data
		$this->formatStagedData();
	}

	/**
	 * Run any unstaging functions to decode processor responses
	 *
	 * @param array $data response data
	 */
	protected function unstageData( $data ) {
		foreach ( $data as $field => $value ) {
			// Run custom unstaging function if available.
			$function_name = 'unstage_' . $field;
			$isUnstaged = $this->executeIfFunctionExists( $function_name );

			// Otherwise, copy the value directly.
			if ( !$isUnstaged ) {
				$this->unstaged_data[$field] = $this->staged_data[$field];
			}
		}
	}

	/**
	 * Format staged data
	 *
	 * Formatting:
	 * - trim - all strings
	 * - truncate - all strings to the maximum length permitted by the gateway
	 */
	public function formatStagedData() {

		foreach ( $this->staged_data as $field => $value ) {

			// Trim all values if they are a string
			$value = is_string( $value ) ? trim( $value ) : $value;

			if ( isset( $this->dataConstraints[ $field ] ) && is_string( $value ) ) {

				// Truncate the field if it has a length specified
				if ( isset( $this->dataConstraints[ $field ]['length'] ) ) {
					$length = (integer) $this->dataConstraints[ $field ]['length'];
				} else {
					$length = false;
				}

				if ( !empty( $length ) && !empty( $value ) ) {
					//Note: This is the very last resort. This should already have been dealt with thoroughly in staging.
					$value = substr( $value, 0, $length );
				}

			} else {
				//$this->logger->debug( 'Field does not exist in $this->dataConstraints[ ' . ( string ) $field . ' ]' );
			}

			$this->staged_data[ $field ] = $value;
		}
	}

 	/**
 	 * Stage: amount
 	 *
 	 * For example: JPY 1000.05 get changed to 100005. This need to be 100000.
 	 * For example: JPY 1000.95 get changed to 100095. This need to be 100000.
 	 */
 	protected function stage_amount() {
 		if ( !$this->getData_Unstaged_Escaped( 'amount' )
			|| !$this->getData_Unstaged_Escaped( 'currency_code' )
		) {
 			//can't do anything with amounts at all. Just go home.
			unset( $this->staged_data['amount'] );
 			return;
 		}

		$amount = $this->getData_Unstaged_Escaped( 'amount' );
		if ( !DataValidator::is_fractional_currency( $this->getData_Unstaged_Escaped( 'currency_code' ) ) ) {
			$amount = floor( $amount );
		}

		$this->staged_data['amount'] = $amount * 100;
	}

	protected function unstage_amount() {
		$this->unstaged_data['amount'] = $this->getData_Staged( 'amount' ) / 100;
	}

	/**
	 * Stage the street address
	 *
	 * In the event that there isn't anything in there, we need to send
	 * something along so that AVS checks get triggered at all.
	 *
	 * The zero is intentional: Allegedly, Some banks won't perform the check
	 * if the address line contains no numerical data.
	 */
	protected function stage_street() {
		$street = '';
		if ( isset( $this->unstaged_data['street'] ) ) {
			$street = trim( $this->unstaged_data['street'] );
		}

		if ( !$street
			|| !DataValidator::validate_not_just_punctuation( $street )
		) {
			$this->staged_data['street'] = 'N0NE PROVIDED'; //The zero is intentional. See function comment.
		}
	}

	/**
	 * Stage the zip / postal code
	 *
	 * In the event that there isn't anything in there, we need to send
	 * something along so that AVS checks get triggered at all.
	 */
	protected function stage_zip() {
		$zip = '';
		if ( isset( $this->unstaged_data['zip'] ) ) {
			$zip = trim( $this->unstaged_data['zip'] );
		}
		if ( strlen( $zip ) === 0 ) {
			//it would be nice to check for more here, but the world has some
			//straaaange postal codes...
			$this->staged_data['zip'] = '0';
		}

		//country-based zip grooming to make AVS (marginally) happy
		switch ( $this->getData_Unstaged_Escaped( 'country' ) ) {
			case 'CA':
				//Canada goes "A0A 0A0"
				$this->staged_data['zip'] = strtoupper( $zip );
				//In the event that they only forgot the space, help 'em out.
				$regex = '/[A-Z]\d[A-Z]\d[A-Z]\d/';
				if ( strlen( $this->staged_data['zip'] ) === 6
					&& preg_match( $regex, $zip )
				) {
					$this->staged_data['zip'] = substr( $zip, 0, 3 ) . ' ' . substr( $zip, 3, 3 );
				}
				break;
		}
	}

	protected function stage_email() {
		if ( empty( $this->staged_data['email'] ) ) {
			$this->staged_data['email'] = $this->getGlobal( 'DefaultEmail' );
		}
	}

	protected function buildRequestParams() {
		// Look up the request structure for our current transaction type in the transactions array
		$structure = $this->getTransactionRequestStructure();
		if ( !is_array( $structure ) ) {
			return '';
		}

		$queryparams = array();

		//we are going to assume a flat array, because... namevalue.
		foreach ( $structure as $fieldname ) {
			$fieldvalue = $this->getTransactionSpecificValue( $fieldname );
			if ( $fieldvalue !== '' && $fieldvalue !== false ) {
				$queryparams[ $fieldname ] = $fieldvalue;
			}
		}

		return $queryparams;
	}

	/**
	 * Public accessor to the $transaction_response variable
	 * @return PaymentTransactionResponse
	 */
	public function getTransactionResponse() {
		return $this->transaction_response;
	}

	/**
	 * Returns the transaction communication status, or false if not set
	 * present.
	 * @return mixed
	 */
	public function getTransactionStatus() {
		if ( $this->transaction_response && $this->transaction_response->getCommunicationStatus() ) {
			return $this->transaction_response->getCommunicationStatus();
		}
		return false;
	}

	/**
	 * If it has been set: returns the final payment status in the $final_status
	 * member variable. This is the one we care about for switching
	 * on overall behavior. Otherwise, returns false.
	 * @return mixed Final Transaction results status, or false if not set.
	 * Should be one of the constants defined in @see FinalStatus
	 */
	public function getFinalStatus() {
		if ( $this->final_status ) {
			return $this->final_status;
		} else {
			return false;
		}
	}

	/**
	 * Sets the final payment status. This is the one we care about for
	 * switching on behavior.
	 * DO NOT SET THE FINAL STATUS unless you've just taken an entire donation
	 * process to completion: This status being set at all, denotes the very end
	 * of the donation process on our end. Further attempts by the same user
	 * will be seen as starting over.
	 * @param string $status The final status of one discrete donation attempt,
	 * can be one of constants defined in @see FinalStatus
	 * @throws UnexpectedValueException
	 */
	public function finalizeInternalStatus( $status ) {

		/**
		 * Handle session stuff!
		 * -Behavior-
		 * * Always, always increment numAttempt.
		 * * complete/pending/pending-poke: Reset for potential totally
		 * new payment, but keep numAttempt and other antifraud things
		 * (velocity data) around.
		 * * failed: KEEP all donor data around unless numAttempt has
		 * hit its max, but kill the ctid (in the likely case that it
		 * was an honest mistake)
		 */
		$this->incrementNumAttempt();
		$force = false;
		switch ( $status ) {
			case FinalStatus::COMPLETE:
			case FinalStatus::PENDING:
			case FinalStatus::PENDING_POKE:
				$force = true;
				break;
			case FinalStatus::FAILED:
			case FinalStatus::REVISED:
				$force = false;
				break;
		}
		$this->session_resetForNewAttempt( $force );

		$this->logFinalStatus( $status );

		$this->sendFinalStatusMessage( $status );

		$this->final_status = $status;
	}

	/**
	 * Easily-child-overridable log component of setting the final
	 * transaction status, which will only ever be set at the very end of a
	 * transaction workflow.
	 * @param string $status one of the constants defined in @see FinalStatus
	 */
	public function logFinalStatus( $status ){
		$action = $this->getValidationAction();

		$msg = " FINAL STATUS: '$status:$action' - ";

		//what do we want in here?
		//Attempted payment type, country of origin, $status, amount... campaign?
		//error message if one exists.
		$keys = array(
			'payment_submethod',
			'payment_method',
			'country',
			'utm_campaign',
			'amount',
			'currency_code',
		);

		foreach ($keys as $key){
			$msg .= $this->getData_Unstaged_Escaped( $key ) . ', ';
		}

		$txn_message = $this->getTransactionMessage();
		if ( $txn_message ){
			$msg .= " $txn_message";
		}

		$this->payment_init_logger->info( $msg );
	}

	/**
	 * Build and send a message to the payments-init queue, once the initial workflow is complete.
	 */
	public function sendFinalStatusMessage( $status ) {
		$transaction = array (
			'php-message-class' => 'SmashPig\CrmLink\Messages\DonationInterfaceFinalStatus',
			'validation_action' => $this->getValidationAction(),
			'payments_final_status' => $status,
		);

		//add more keys here if you want it in the db equivalent of the payments-init queue.
		//for now, though, just taking the ones that make it to the logs.
		$keys = array (
			'payment_submethod',
			'payment_method',
			'country',
			'amount',
			'currency_code',
		);

		foreach ( $keys as $key ) {
			$transaction[$key] = $this->getData_Unstaged_Escaped( $key );
		}

		$transaction = $this->makeFreeformStompTransaction( $transaction );

		try {
			// FIXME: Dispatch "freeform" messages transparently as well.
			// TODO: write test
			$this->logger->info( 'Pushing transaction to payments-init queue.' );
			DonationQueue::instance()->push( $transaction, 'payments-init' );
		} catch ( Exception $e ) {
			$this->logger->error( 'Unable to send payments-init message' );
		}
	}

	/**
	 * @deprecated
	 * @return string|boolean
	 */
	public function getTransactionMessage() {
		if ( $this->transaction_response && $this->transaction_response->getTxnMessage() ) {
			return $this->transaction_response->getTxnMessage();
		}
		return false;
	}

	/**
	 * @deprecated
	 * @return string|boolean
	 */
	public function getTransactionGatewayTxnID() {
		if ( $this->transaction_response && $this->transaction_response->getGatewayTransactionId() ) {
			return $this->transaction_response->getGatewayTransactionId();
		}
		return false;
	}

	/**
	 * Returns the FORMATTED data harvested from the reply, or false if it is not set.
	 * @return mixed An array of returned data, or false.
	 */
	public function getTransactionData() {
		if ( $this->transaction_response && $this->transaction_response->getData() ) {
			return $this->transaction_response->getData();
		}
		return false;
	}

	/**
	 * Returns an array of errors, in the format $error_code => $error_message.
	 * This should be an empty array on transaction success.
	 *
	 * @deprecated
	 *
	 * @return array
	 */
	public function getTransactionErrors() {

		if ( $this->transaction_response && $this->transaction_response->getErrors() ) {
			$simplify = function( $error ) {
				return $error['message'];
			};
			return array_map( $simplify, $this->transaction_response->getErrors() );
		} else {
			return array();
		}
	}

	public function getFormClass() {
		return 'Gateway_Form_RapidHtml';
	}

	public function getGatewayAdapterClass() {
		return get_called_class();
	}

	//only the gateway should be setting validation errors. Everybody else should set manual errors.
	protected function setValidationErrors( $errors ) {
		$this->validation_errors = $errors;
	}

	public function getValidationErrors() {
		if ( !empty( $this->validation_errors ) ) {
			return $this->validation_errors;
		} else {
			return false;
		}
	}

	public function addManualError( $errors, $reset = false ) {
		if ( $reset ){
			$this->manual_errors = array();
			return;
		}
		$this->manual_errors = array_merge( $this->manual_errors, $errors );
	}

	public function getManualErrors() {
		if ( !empty( $this->manual_errors ) ) {
			return $this->manual_errors;
		} else {
			return false;
		}
	}

	public function getAllErrors(){
		$validation = $this->getValidationErrors();
		$manual = $this->getManualErrors();
		$return = array();
		if ( is_array( $validation ) ){
			$return = array_merge( $return, $validation );
		}
		if ( is_array( $manual ) ){
			$return = array_merge( $return, $manual );
		}
		return $return;
	}

	/**
	 * Adds one to the 'numAttempt' field we use to keep track of how many
	 * times a donor has attempted a payment, in a session.
	 * When they first show up (or get their token/session reset), it should
	 * be set to '0'.
	 */
	protected function incrementNumAttempt() {
		self::session_ensure();
		$attempts = self::session_getData( 'numAttempt' ); //intentionally outside the 'Donor' key.
		if ( is_numeric( $attempts ) ) {
			$attempts += 1;
		} else {
			//assume garbage = 0, so...
			$attempts = 1;
		}

		$_SESSION['numAttempt'] = $attempts;
	}

	/**
	 * Some payment gateways require a distinct identifier for each API call
	 * or for each new payment attempt, even if retrying an attempt that failed
	 * validation.  This is slightly different from numAttempt, which is only
	 * incremented when setting a final status for a payment attempt.
	 * It is the child class's responsibility to increment this at the
	 * appropriate time.
	 */
	protected function incrementSequenceNumber() {
		self::session_ensure();
		$sequence = self::session_getData( 'sequence' ); //intentionally outside the 'Donor' key.
		if ( is_numeric( $sequence ) ) {
			$sequence += 1;
		} else {
			$sequence = 1;
		}

		$_SESSION['sequence'] = $sequence;
	}

	public function setHash( $hashval ) {
		$this->dataObj->setVal( 'data_hash', $hashval );
	}

	public function unsetHash() {
		$this->dataObj->expunge( 'data_hash' );
	}

	/**
	 * Runs all the pre-process hooks that have been enabled and configured in
	 * donationdata.php and/or LocalSettings.php
	 * This function is most likely to be called through
	 * executeFunctionIfExists, early on in do_transaction.
	 */
	function runAntifraudHooks() {
		//extra layer of Stop Doing This.
		$errors = $this->getTransactionErrors();
		if ( !empty( $errors ) ) {
			$this->logger->info( 'Skipping antifraud hooks: Transaction is already in error' );
			return;
		}
		// allow any external validators to have their way with the data
		$this->logger->info( 'Preparing to run custom filters' );
		WmfFramework::runHooks( 'GatewayValidate', array( &$this ) );
		$this->logger->info( 'Finished running custom filters' );

		//DO NOT set some variable as getValidationAction() here, and keep
		//checking that. getValidationAction could change with each one of these
		//hooks, and this ought to cascade.
		// if the transaction was flagged for review
		if ( $this->getValidationAction() == 'review' ) {
			// expose a hook for external handling of trxns flagged for review
			WmfFramework::runHooks( 'GatewayReview', array( &$this ) );
		}

		// if the transaction was flagged to be 'challenged'
		if ( $this->getValidationAction() == 'challenge' ) {
			// expose a hook for external handling of trxns flagged for challenge (eg captcha)
			WmfFramework::runHooks( 'GatewayChallenge', array( &$this ) );
		}

		// if the transaction was flagged for rejection
		if ( $this->getValidationAction() == 'reject' ) {
			// expose a hook for external handling of trxns flagged for rejection
			WmfFramework::runHooks( 'GatewayReject', array( &$this ) );
		}
	}

	/**
	 * Runs all the post-process hooks that have been enabled and configured in
	 * donationdata.php and/or LocalSettings.php, including the ActiveMQ/Stomp
	 * hooks.
	 * This function is most likely to be called through
	 * executeFunctionIfExists, later on in do_transaction.
	 */
	protected function runPostProcessHooks() {
		// expose a hook for any post processing
		WmfFramework::runHooks( 'GatewayPostProcess', array( &$this ) );

		$this->doStompTransaction();
	}

	protected function pushMessage( $queue ) {
		$this->logger->info( "Pushing transaction to queue [$queue]" );
		DonationQueue::instance()->push( $this->getStompTransaction(), $queue );
	}

	protected function setLimboMessage( $queue = 'limbo' ) {
		// FIXME: log the key and raw queue name.
		$this->logger->info( "Setting transaction in limbo store [$queue]" );
		DonationQueue::instance()->set( $this->getCorrelationID(), $this->getStompTransaction(), $queue );
	}

	protected function deleteLimboMessage( $queue = 'limbo' ) {
		$this->logger->info( "Clearing transaction from limbo store [$queue]" );
		try {
			DonationQueue::instance()->delete( $this->getCorrelationID(), $queue );
		} catch( BadMethodCallException $ex ) {
			$this->logger->warning( "Backend for queue [$queue] does not support deletion.  Hope your message had an expiration date!" );
		}
	}

	/**
	 * If there are things about a transaction that we need to stash in the
	 * transaction's definition (defined in a local defineTransactions() ), we
	 * can recall them here. Currently, this is only being used to determine if
	 * we have a transaction whose transmission would require multiple attempts
	 * to wait for a certain status (or set of statuses), but we could do more
	 * with this mechanism if we need to.
	 * @param string $option_value the name of the key we're looking for in the
	 * transaction definition.
	 * @return mixed the transaction's value for that key if it exists, or NULL.
	 */
	protected function transaction_option( $option_value ) {
		//ooo, ugly.
		$transaction = $this->getCurrentTransaction();
		if ( !$transaction ){
			return NULL;
		}
		if ( array_key_exists( $option_value, $this->transactions[$transaction] ) ) {
			return $this->transactions[$transaction][$option_value];
		}
		return NULL;
	}

	/**
	 * Instead of pulling all the DonationData back through to update one local
	 * value, use this. It updates both staged_data (which is intended to be
	 * staged and used _just_ by the gateway) and unstaged_data, which is actually
	 * just normalized and sanitized form data as entered by the user.
	 *
	 * TODO: handle the cases where $val is listed in the gateway adapter's
	 * staged_vars.
	 * Not doing this right now, though, because it's not yet necessary for
	 * anything we have at the moment.
	 *
	 * @param string $val The field name that we are looking to retrieve from
	 * our DonationData object.
	 */
	function refreshGatewayValueFromSource( $val ) {
		$refreshed = $this->dataObj->getVal_Escaped( $val );
		if ( !is_null($refreshed) ){
			$this->staged_data[$val] = $refreshed;
			$this->unstaged_data[$val] = $refreshed;
		} else {
			unset( $this->staged_data[$val] );
			unset( $this->unstaged_data[$val] );
		}
	}

	/**
	 * Allows us to send an initial fraud score offset with api calls
	 */
	public function addRiskScore( $score ) {
		$this->risk_score += $score;
	}

	/**
	 * Sets the current validation action. This is meant to be used by the
	 * process hooks, and as such, by default, only worse news than was already
	 * being stored will be retained for the final result.
	 * @param string $action the value you want to set as the action.
	 * @param bool $reset set to true to do a hard set on the action value.
	 * Otherwise, the status will only change if it fails harder than it already
	 * was.
	 * @throws UnexpectedValueException
	 */
	public function setValidationAction( $action, $reset = false ) {
		//our choices are:
		$actions = array(
			'process' => 0,
			'review' => 1,
			'challenge' => 2,
			'reject' => 3,
		);
		if ( !isset( $actions[$action] ) ) {
			throw new UnexpectedValueException( "Action $action is invalid." );
		}

		if ( $reset ) {
			$this->action = $action;
			return;
		}

		if ( ( int ) $actions[$action] > ( int ) $actions[$this->getValidationAction()] ) {
			$this->action = $action;
		}
	}

	/**
	 * Returns the current validation action.
	 * This will typically get set and altered by the various enabled process hooks.
	 * @return string the current process action.
	 */
	public function getValidationAction() {
		if ( !isset( $this->action ) ) {
			$this->action = 'process';
		}
		return $this->action;
	}

	/**
	 * Lets the outside world (particularly hooks that accumulate points scores)
	 * know if we are a batch processor.
	 * @return type
	 */
	public function isBatchProcessor(){
		return $this->batch;
	}

	/**
	 * Tell the gateway that it is going to be used for an API request, so
	 * it can bypass setting up all the visual components.
	 * @param boolean $set True if this is an API request, false if not.
	 */
	public function setApiRequest( $set = true ) {
		$this->api_request = $set;
	}

	/**
	 * Find out if we're an API request or not.
	 * @return boolean true if we are, otherwise false.
	 */
	public function isApiRequest() {
		if ( !property_exists( $this, 'api_request' ) ) {
			return false;
		} else {
			return $this->api_request;
		}
	}

	public function getOriginalValidationErrors( ){
		return $this->dataObj->getValidationErrors();
	}

	/**
	 * Build list of required fields
	 * TODO: Determine if this ever needs to be overridden per gateway, or if
	 * all the per-country / per-gateway cases can be expressed declaratively
	 * in payment method / submethod metadata.  If that's the case, move this
	 * function (to DataValidator?)
	 * @return array of field names (empty if no payment method set)
	 */
	public function getRequiredFields() {
		$required_fields = array();
		if ( !$this->getPaymentMethod() ) {
			return $required_fields;
		}

		$methodMeta = $this->getPaymentMethodMeta();
		$validation = isset( $methodMeta['validation'] ) ? $methodMeta['validation'] : array();

		if ( $this->getPaymentSubmethod() ) {
			$submethodMeta = $this->getPaymentSubmethodMeta();
			if ( isset( $submethodMeta['validation'] ) ) {
				// submethod validation can override method validation
				// TODO: child method anything should supercede parent method
				// anything, and PaymentMethod should handle that.
				$validation = $submethodMeta['validation'] + $validation;
			}
		}

		foreach ( $validation as $type => $enabled ) {
			if ( $enabled !== true ) {
				continue;
			}

			switch ( $type ) {
			case 'address' :
				$check_not_empty = array(
					'street',
					'city',
					'state',
					'country',
					'zip', //this should really be added or removed, depending on the country and/or gateway requirements.
					//however, that's not happening in this class in the code I'm replacing, so...
					//TODO: Something clever in the DataValidator with data groups like these.
					);
					break;
				case 'amount' :
					$check_not_empty = array( 'amount' );
					break;
				case 'creditCard' :
					$check_not_empty = array(
						'card_num',
						'cvv',
						'expiration',
						'card_type'
					);
					break;
				case 'email' :
					$check_not_empty = array( 'email' );
					break;
				case 'name' :
					$check_not_empty = array(
						'fname',
						'lname'
					);
					break;
				case 'fiscal_number' :
					$check_not_empty = array( 'fiscal_number' );
					break;
				default:
					$this->logger->error( "bad required group name: {$type}" );
					continue;
			}

			if ( $check_not_empty ) {
				$required_fields = array_unique( array_merge( $required_fields, $check_not_empty ) );
			}
		}

		return $required_fields;
	}

	/**
	 * Check donation data for validity
	 *
	 * @return boolean true if validation passes
	 *
	 * TODO: Maybe validate on $unstaged_data directly? 
	 */
	public function revalidate() {
		$check_not_empty = $this->getRequiredFields();

		$validation_errors = $this->dataObj->getValidationErrors( true, $check_not_empty );
		$this->setValidationErrors( $validation_errors );
		return $this->validatedOK();
	}

	public function validatedOK(){
		if ( $this->getValidationErrors() === false ){
			return true;
		}
		return false;
	}

	/**
	 * This custom filter function checks the global variable:
	 *
	 * CountryMap
	 *
	 * How the score is tabulated:
	 *  - If a country is not defined, a score of zero will be generated.
	 *  - Generates a score based on the defined value.
	 *  - Returns an integer: 0 <= $score <= 100
	 *
	 * @see $wgDonationInterfaceCustomFiltersFunctions
	 * @see $wgDonationInterfaceCountryMap
	 *
	 * @return integer
	 */
	public function getScoreCountryMap() {

		$score = 0;

		$country = $this->getData_Unstaged_Escaped( 'country' );

		$countryMap = $this->getGlobal( 'CountryMap' );

		$msg = self::getGatewayName() . ': Country map: '
			. print_r( $countryMap, true );

		$this->logger->debug( $msg );

		// Lookup a score if it is defined
		if ( isset( $countryMap[ $country ] ) ) {
			$score = (integer) $countryMap[ $country ];
		}

		// @see $wgDonationInterfaceDisplayDebug
		$this->debugarray[] = 'custom filters function: get country [ '
			. $country . ' ] map score = ' . $score;

		return $score;
	}

	/**
	 * This custom filter function checks the global variable:
	 *
	 * EmailDomainMap
	 *
	 * How the score is tabulated:
	 *  - If a emailDomain is not defined, a score of zero will be generated.
	 *  - Generates a score based on the defined value.
	 *  - Returns an integer: 0 <= $score <= 100
	 *
	 * @see $wgDonationInterfaceCustomFiltersFunctions
	 * @see $wgDonationInterfaceEmailDomainMap
	 *
	 * @return integer
	 */
	public function getScoreEmailDomainMap() {

		$score = 0;

		$email = $this->getData_Unstaged_Escaped( 'email' );

		$emailDomain = substr( strstr( $email, '@' ), 1 );

		$emailDomainMap = $this->getGlobal( 'EmailDomainMap' );

		$msg = self::getGatewayName() . ': Email Domain map: '
			. print_r( $emailDomainMap, true );

		$this->logger->debug( $msg );

		// Lookup a score if it is defined
		if ( isset( $emailDomainMap[ $emailDomain ] ) ) {
			$score = (integer) $emailDomainMap[ $emailDomain ];
		}

		// @see $wgDonationInterfaceDisplayDebug
		$this->debugarray[] = 'custom filters function: get email domain [ '
			. $emailDomain . ' ] map score = ' . $score;

		return $score;
	}

	/**
	 * This custom filter function checks the global variable:
	 *
	 * UtmCampaignMap
	 *
	 * @TODO: All these regex map matching functions that are identical with
	 * different internal var names are making me rilly mad. Collapse.
	 *
	 * How the score is tabulated:
	 *  - Add the score(value) associated with each regex(key) in the map var.
	 *
	 * @see $wgDonationInterfaceCustomFiltersFunctions
	 * @see $wgDonationInterfaceUtmCampaignMap
	 *
	 * @return integer
	 */
	public function getScoreUtmCampaignMap() {

		$score = 0;

		$campaign = $this->getData_Unstaged_Escaped( 'utm_campaign' );
		$campaignMap = $this->getGlobal( 'UtmCampaignMap' );

		$msg = self::getGatewayName() . ': UTM Campaign map: '
			. print_r( $campaignMap, true );

		$this->logger->debug( $msg );

		// If any of the defined regex patterns match, add the points.
		if ( is_array( $campaignMap ) && !empty( $campaignMap ) ){
			foreach ( $campaignMap as $regex => $points ){
				if ( preg_match( $regex, $campaign ) ) {
					$score = (integer) $points;
				}
			}
		}

		// @see $wgDonationInterfaceDisplayDebug
		$this->debugarray[] = 'custom filters function: get utm campaign [ '
			. $campaign . ' ] score = ' . $score;

		return $score;
	}

	/**
	 * This custom filter function checks the global variable:
	 *
	 * UtmMediumMap
	 *
	 * @TODO: Again. Regex map matching functions, identical, with minor
	 * internal var names. Collapse.
	 *
	 * How the score is tabulated:
	 *  - Add the score(value) associated with each regex(key) in the map var.
	 *
	 * @see $wgDonationInterfaceCustomFiltersFunctions
	 * @see $wgDonationInterfaceUtmMediumMap
	 *
	 * @return integer
	 */
	public function getScoreUtmMediumMap() {

		$score = 0;

		$medium = $this->getData_Unstaged_Escaped( 'utm_medium' );
		$mediumMap = $this->getGlobal( 'UtmMediumMap' );

		$msg = self::getGatewayName() . ': UTM Medium map: '
			. print_r( $mediumMap, true );

		$this->logger->debug( $msg );

		// If any of the defined regex patterns match, add the points.
		if ( is_array( $mediumMap ) && !empty( $mediumMap ) ){
			foreach ( $mediumMap as $regex => $points ){
				if ( preg_match( $regex, $medium ) ) {
					$score = (integer) $points;
				}
			}
		}

		// @see $wgDonationInterfaceDisplayDebug
		$this->debugarray[] = 'custom filters function: get utm medium [ '
			. $medium . ' ] score = ' . $score;

		return $score;
	}

	/**
	 * This custom filter function checks the global variable:
	 *
	 * UtmSourceMap
	 *
	 * @TODO: Argharghargh, inflated code! Collapse!
	 *
	 * How the score is tabulated:
	 *  - Add the score(value) associated with each regex(key) in the map var.
	 *
	 * @see $wgDonationInterfaceCustomFiltersFunctions
	 * @see $wgDonationInterfaceUtmSourceMap
	 *
	 * @return integer
	 */
	public function getScoreUtmSourceMap() {

		$score = 0;

		$source = $this->getData_Unstaged_Escaped( 'utm_source' );
		$sourceMap = $this->getGlobal( 'UtmSourceMap' );

		$msg = self::getGatewayName() . ': UTM Source map: '
			. print_r( $sourceMap, true );

		$this->logger->debug( $msg );

		// If any of the defined regex patterns match, add the points.
		if ( is_array( $sourceMap ) && !empty( $sourceMap ) ){
			foreach ( $sourceMap as $regex => $points ){
				if ( preg_match( $regex, $source ) ) {
					$score = (integer) $points;
				}
			}
		}

		// @see $wgDonationInterfaceDisplayDebug
		$this->debugarray[] = 'custom filters function: get utm source [ '
			. $source . ' ] score = ' . $score;

		return $score;
	}

	/**
	 * For places that might need the merchant ID outside of the adapter
	 */
	public function getMerchantID() {
		return $this->account_config[ 'MerchantID' ];
	}

	/**
	 * Check to see if the session exists.
	 */
	public static function session_exists() {
		if ( session_id() ) {
			return true;
		}
		return false;
	}

	/**
	 * session_ensure
	 * Ensure that we have a session set for the current user.
	 * If we do not have a session set for the current user,
	 * start the session.
	 */
	public static function session_ensure() {
		// if the session is already started, do nothing
		if ( self::session_exists() ) {
			return;
		}

		// otherwise, fire it up using global mw function wfSetupSession
		WmfFramework::setupSession();
	}

	/**
	 * Retrieve data from the sesion if it's set, and null if it's not.
	 * @param string $key The array key to return from the session.
	 * @param string $subkey Optional: The subkey to return from the session.
	 * Only really makes sense if $key is an array.
	 * @return mixed The session value if present, or null if it is not set.
	 */
	public static function session_getData( $key, $subkey = null ) {
		if ( is_array( $_SESSION ) && array_key_exists( $key, $_SESSION ) ) {
			if ( is_null( $subkey ) ) {
				return $_SESSION[$key];
			} else {
				if ( is_array( $_SESSION[$key] ) && array_key_exists( $subkey, $_SESSION[$key] ) ) {
					return $_SESSION[$key][$subkey];
				}
			}
		}
		return null;
	}

	/**
	 * Checks to see if we have donor data in our session.
	 * This can be useful for determining if a user should be at a certain point
	 * in the workflow for certain gateways. For example: This is used on the
	 * outside of the adapter in GlobalCollect's resultswitcher page, to
	 * determine if the user is actually in the process of making a credit card
	 * transaction.
	 * @param bool|string $key Optional: A particular key to check against the
	 * donor data in session.
	 * @param string $value Optional (unless $key is set): A value that the $key
	 * should contain, in the donor session.
	 * @return boolean true if the session contains donor data (and if the data
	 * key matches, when key and value are set), and false if there is no donor
	 * data (or if the key and value do not match)
	 */
	public static function session_hasDonorData( $key = false, $value = '' ) {
		if ( self::session_exists() && !is_null( self::session_getData( 'Donor' ) ) ) {
			if ( $key === false ) {
				return true;
			}
			if ( self::session_getData( 'Donor', $key ) === $value ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Unsets the session data, in the case that we've saved it for gateways
	 * like GlobalCollect that require it to persist over here through their
	 * iframe experience.
	 */
	public static function session_unsetDonorData() {
		if ( self::session_hasDonorData() ) {
			unset( $_SESSION['Donor'] );
		}
	}

	/**
	 * Removes any old donor data from the session, and adds the current set.
	 * This will be used internally every time we call do_transaction.
	 */
	public function session_addDonorData() {
		$this->logger->info( __FUNCTION__ . ': Refreshing all donor data' );
		self::session_ensure();
		$_SESSION['Donor'] = array ( );
		$donordata = DonationData::getStompMessageFields();
		$donordata[] = 'order_id';

		foreach ( $donordata as $item ) {
			$_SESSION['Donor'][$item] = $this->getData_Unstaged_Escaped( $item );
		}
	}

	/**
	 * This should kill the session as hard as possible.
	 * It will leave the cookie behind, but everything it could possibly
	 * reference will be gone.
	 */
	public function session_killAllEverything() {
		//yes: We do need all of these things, to be sure we're killing the
		//correct session data everywhere it could possibly be.
		self::session_ensure(); //make sure we are killing the right thing.
		session_unset(); //frees all registered session variables. At this point, they can still be re-registered.
		session_destroy(); //killed on the server.
	}

	/**
	 * Destroys the session completely.
	 * ...including session velocity data, and the form stack. So, you
	 * probably just shouldn't. Please consider session_reset instead. Please.
	 * Note: This will leave the cookie behind! It just won't go to anything at
	 * all.
	 */
	public function session_unsetAllData() {
		$this->session_killAllEverything();
		$this->debugarray[] = 'Killed all the session everything.';
	}

	/**
	 * For those times you want to have the user functionally start over
	 * without, you know, cutting your entire head off like you do with
	 * session_unsetAllData().
	 * @param string $force Behavior Description:
	 * $force = true: Reset for potential totally new payment, but keep
	 * numAttempt and other antifraud things (velocity data) around.
	 * $force = false: Keep all donor data around unless numAttempt has hit
	 * its max, but kill the ctid (in the likely case that it was an honest
	 * mistake)
	 */
	public function session_resetForNewAttempt( $force = false ) {
		$reset = $force;
		if ( self::session_getData( 'numAttempt' ) > 3 ) {
			$reset = true;
			$_SESSION['numAttempt'] = 0;
		}

		if ( $reset ) {
			$this->logger->info( __FUNCTION__ . ': Unsetting session donor data' );
			$this->session_unsetDonorData();
			//leave the payment forms and antifraud data alone.
			//but, under no circumstances should the gateway edit
			//token appear in the preserve array...
			$preserve_main = array (
				'DonationInterface_SessVelocity',
				'PaymentForms',
				'numAttempt',
				'order_status', //for post-payment activities
				'sequence',
			);
			$msg = '';
			foreach ( $_SESSION as $key => $value ) {
				if ( !in_array( $key, $preserve_main ) ) {
					$msg .= "$key, "; //always one extra comma; Don't care.
					unset( $_SESSION[$key] );
				}
			}
			if ( $msg != '' ) {
				$this->logger->info( __FUNCTION__ . ": Unset the following session keys: $msg" );
			}
		} else {
			//I'm sure we could put more here...
			$soft_reset = array (
				'order_id',
			);
			foreach ( $soft_reset as $reset_me ) {
				unset( $_SESSION['Donor'][$reset_me] );
			}
			$this->logger->info( __FUNCTION__ . ': Soft reset, order_id only' );
		}
	}

	/**
	 * Check to see if donor is making a repeated attempt that is incompatible
	 * with the previous attempt, such as a gateway changes.  Reset certain
	 * things if so.  Prevents order_id leakage, log spam, and recur problems.
	 * FIXME: this all has to be special cases because we need to compare
	 * session values with request values that are normalized by DonationData,
	 * and DonationData's idea of normalization includes some stuff we don't
	 * want to do yet, like assigning order ID and saving contribution tracking.
	 */
	protected function session_resetOnSwitch() {
		if ( !$this->session_exists() ) {
			return;
		}
		$oldData = $this->session_getData( 'Donor' );
		if ( !$oldData ) {
			return;
		}

		// If the gateway has changed, reset everything
		$newGateway = $this->getIdentifier();
		if ( !empty( $oldData['gateway'] ) && $oldData['gateway'] !== $newGateway ) {
			$this->logger->info(
				"Gateway changed from {$oldData['gateway']} to $newGateway.  Resetting session."
			);
			$this->session_resetForNewAttempt( true );
			return;
		}

		// Now compare session with current request parameters
		$newRequest = RequestContext::getMain()->getRequest();
		// Reset submethod when method changes to avoid form mismatch errors
		if ( !empty( $oldData['payment_method'] ) && !empty( $oldData['payment_submethod'] ) ) {
			// Cut down version of the normalization from DonationData
			$newMethod = null;
			foreach( array( 'payment_method', 'paymentmethod' ) as $key ) {
				if ( $newRequest->getVal( $key ) ) {
					$newMethod = $newRequest->getVal( $key );
				}
			}
			if ( $newMethod ) {
				$parts = explode( '.', $newMethod );
				$newMethod = $parts[0];
				if ( $newMethod !== $oldData['payment_method'] ) {
					$this->logger->info(
						"Payment method changed from {$oldData['payment_method']} to $newMethod.  Unsetting submethod."
					);
					unset( $_SESSION['Donor']['payment_submethod'] );
				}
			}
		}

		// Don't reuse order IDs between recurring and non-recurring donations
		// Recurring is stored in session as '1' for true and '' for false
		// Only reset if there is an explicit querystring parameter.
		if ( isset( $oldData['recurring'] ) && !empty( $oldData['order_id'] ) ) {
			$newRecurring = '';
			$hasRecurParam = false;
			foreach( array( 'recurring_paypal', 'recurring' ) as $key ) {
				$newVal = $newRequest->getVal( $key );
				if ( $newVal !== null ) {
					$hasRecurParam = true;
				}
				if ( $newVal === '1' || $newVal === 'true' ) {
					$newRecurring = '1';
				}
			}
			if ( $hasRecurParam && ( $newRecurring !== $oldData['recurring'] ) ) {
				$this->logger->info(
					"Recurring changed from '{$oldData['recurring']}' to '$newRecurring'.  Unsetting order ID."
				);
				unset( $_SESSION['Donor']['order_id'] );
			}
		}
	}

	/**
	 * Add a RapidHTML Form (ffname) to this abridged history of where we've
	 * been in this session. This lets us do things like construct useful
	 * "back" links that won't crush all session everything.
	 * @param string $form_key The 'ffname' that RapidHTML uses to load a
	 * payments form. Additional: ffname maps to a first-level key in
	 * $wgDonationInterfaceAllowedHtmlForms
	 */
	public function session_pushRapidHTMLForm( $form_key ) {
		if ( !$form_key ) {
			return;
		}

		self::session_ensure();

		if ( !is_array( self::session_getData( 'PaymentForms' ) ) ) {
			$_SESSION['PaymentForms'] = array ( );
		}

		//don't want duplicates
		if ( $this->session_getLastRapidHTMLForm() != $form_key ) {
			$_SESSION['PaymentForms'][] = $form_key;
		}
	}

	/**
	 * Get the 'ffname' of the last RapidHTML payment form that successfully
	 * loaded for this session.
	 * @return mixed ffname of the last valid payments form if there is one,
	 * otherwise false.
	 */
	public function session_getLastRapidHTMLForm() {
		self::session_ensure();
		if ( !is_array( self::session_getData( 'PaymentForms' ) ) ) {
			return false;
		} else {
			$ffname = end( $_SESSION['PaymentForms'] );
			if ( !$ffname ) {
				return false;
			}
			$data = $this->getData_Unstaged_Escaped();
			//have to check to see if the last loaded form is *still* valid.
			if ( GatewayFormChooser::isValidForm(
				$ffname, $data['country'], $data['currency_code'], $data['payment_method'], $data['payment_submethod'], $data['recurring'], $data['gateway'] )
			) {
				return $ffname;
			} else {
				return false;
			}
		}
	}

	/**
	 * token_applyMD5AndSalt
	 * Takes a clear-text token, and returns the MD5'd result of the token plus
	 * the configured gateway salt.
	 * @param string $clear_token The original, unsalted, unencoded edit token.
	 * @return string The salted and MD5'd token.
	 */
	protected static function token_applyMD5AndSalt( $clear_token ) {
		$salt = self::getGlobal( 'Salt' );

		if ( is_array( $salt ) ) {
			$salt = implode( "|", $salt );
		}

		$salted = md5( $clear_token . $salt ) . User::EDIT_TOKEN_SUFFIX;
		return $salted;
	}

	/**
	 * token_generateToken
	 * Generate a random string to be used as an edit token.
	 * @param string $padding A string with which we could pad out the random hex
	 * further.
	 * @return string
	 */
	public static function token_generateToken( $padding = '' ) {
		$token = dechex( mt_rand() ) . dechex( mt_rand() );
		return md5( $token . $padding );
	}

	/**
	 * Establish an 'edit' token to help prevent CSRF, etc.
	 *
	 * We use this in place of $wgUser->editToken() b/c currently
	 * $wgUser->editToken() is broken (apparently by design) for
	 * anonymous users.  Using $wgUser->editToken() currently exposes
	 * a security risk for non-authenticated users.  Until this is
	 * resolved in $wgUser, we'll use our own methods for token
	 * handling.
	 *
	 * Public so the api can get to it.
	 *
	 * @return string
	 */
	public static function token_getSaltedSessionToken() {
		// make sure we have a session open for tracking a CSRF-prevention token
		self::session_ensure();

		$gateway_ident = self::getIdentifier();

		if ( !isset( $_SESSION[$gateway_ident . 'EditToken'] ) ) {
			// generate unsalted token to place in the session
			$token = self::token_generateToken();
			$_SESSION[$gateway_ident . 'EditToken'] = $token;
		} else {
			$token = $_SESSION[$gateway_ident . 'EditToken'];
		}

		return self::token_applyMD5AndSalt( $token );
	}

	/**
	 * token_refreshAllTokenEverything
	 * In the case where we have an expired session (token mismatch), we go
	 * ahead and fix it for 'em for their next post. We do this by refreshing
	 * everything that has to do with the edit token.
	 */
	protected function token_refreshAllTokenEverything() {
		$unsalted = self::token_generateToken();
		$gateway_ident = self::getIdentifier();
		self::session_ensure();
		$_SESSION[$gateway_ident . 'EditToken'] = $unsalted;
		$salted = $this->token_getSaltedSessionToken();

		$this->addRequestData( array ( 'token' => $salted ) );
	}

	/**
	 * token_matchEditToken
	 * Determine the validity of a token by checking it against the salted
	 * version of the clear-text token we have already stored in the session.
	 * On failure, it resets the edit token both in the session and in the form,
	 * so they will match on the user's next load.
	 *
	 * @var string $val
	 * @return bool
	 */
	protected function token_matchEditToken( $val ) {
		// When fetching the token from the URL (like we do for Worldpay), the last
		// portion may be mangled by + being substituted for ' '. Normally this is
		// valid URL unescaping, but not in this case.
		$val = str_replace( ' ', '+', $val );

		// fetch a salted version of the session token
		$sessionSaltedToken = $this->token_getSaltedSessionToken();
		if ( $val != $sessionSaltedToken ) {
			$this->logger->debug( __FUNCTION__ . ": broken session data\n" );
			//and reset the token for next time.
			$this->token_refreshAllTokenEverything();
		}
		return $val === $sessionSaltedToken;
	}

	/**
	 * token_checkTokens
	 * The main function to check the salted and MD5'd token we should have
	 * saved and gathered from $wgRequest, against the clear-text token we
	 * should have saved to the user's session.
	 * token_getSaltedSessionToken() will start off the process if this is a
	 * first load, and there's no saved token in the session yet.
	 * @staticvar string $match
	 * @return type
	 */
	protected function token_checkTokens() {
		static $match = null; //because we only want to do this once per load.

		if ( $match === null ) {
			// establish the edit token to prevent csrf
			$token = $this->token_getSaltedSessionToken();

			$this->logger->debug( 'editToken: ' . $token );

			// match token
			if ( !$this->dataObj->isSomething( 'token' ) ) {
				$this->addRequestData( array ( 'token' => $token ) );
			}
			$token_check = $this->getData_Unstaged_Escaped( 'token' );

			$match = $this->token_matchEditToken( $token_check );
			if ( $this->dataObj->wasPosted() ) {
				$this->logger->debug( 'Submitted edit token: ' . $this->getData_Unstaged_Escaped( 'token' ) );
				$this->logger->debug( 'Token match: ' . ($match ? 'true' : 'false' ) );
			}
		}

		return $match;
	}

	/**
	 * Retrieve the data we will need in order to retry a payment.
	 * This is useful in the event that we have just killed a session before
	 * the next retry.
	 * @return array Data required for a payment retry.
	 */
	public function getRetryData() {
		$params = array ( );
		foreach ( $this->dataObj->getRetryFields() as $field ) {
			$params[$field] = $this->getData_Unstaged_Escaped( $field );
		}
		return $params;
	}

	/**
	 * isValidSpecialForm: Tells us if the ffname supplied is a valid
	 * special form for the current gateway.
	 * @var string $ffname The form name we want to try
	 * @return boolean True if this is a valid special form, otherwise false
	 */
	public function isValidSpecialForm( $ffname ){
		$defn = GatewayFormChooser::getFormDefinition( $ffname );
		if ( is_array( $defn ) &&
			DataValidator::value_appears_in( $this->getIdentifier(), $defn['gateway'] ) &&
			array_key_exists( 'special_type', $defn ) ){
				return true;
		}
		return false;
	}

	/**
	 * Make sure that we've got a valid ffname so we don't have to screw
	 * around with this in RapidHTML when we try to load it and fail.
	 */
	public function setValidForm() {
		//do we even need the visual stuff?
		if ( $this->isApiRequest() || $this->isBatchProcessor() ) {
			return;
		}

		//check to see if the current ffname exists, and is loadable.
		$data = $this->getData_Unstaged_Escaped();

		$ffname = null;
		if ( isset( $data['ffname'] ) ) {
			$ffname = $data['ffname'];

			//easy stuff first:
			if ( $this->isValidSpecialForm( $ffname ) ) {
				return;
			}
		}

//		'country' might = 'XX' - CN does this when it's deeply confused.
		if ( !isset( $data['country'] ) || $data['country'] === 'XX' ) {
			$country = null;
		} else {
			$country = $data['country'];
		}

		//harumph. Remind me again why I hate @ suppression so much?
		$currency = isset( $data['currency_code'] ) ? $data['currency_code'] : null;
		$payment_method = isset( $data['payment_method'] ) ? $data['payment_method'] : null;
		$payment_submethod = isset( $data['payment_submethod'] ) ? $data['payment_submethod'] : null;
		$recurring = isset( $data['recurring'] ) ? $data['recurring'] : null;
		$gateway = isset( $data['gateway'] ) ? $data['gateway'] : null;

		//for the error messages
		$utm = isset( $data['utm_source'] ) ? $data['utm_source'] : null;
		$ref = isset( $data['referrer'] ) ? $data['referrer'] : null;
		//make it actually possible to debug this hot mess

		$this->logger->info( "Attempting to set a valid form for the combination: " . $this->getLogDebugJSON() );

		if ( !is_null( $ffname ) && GatewayFormChooser::isValidForm( $ffname, $country, $currency, $payment_method, $payment_submethod, $recurring, $gateway ) ) {
			return;
		} else if ( $this->session_getLastRapidHTMLForm() ) { //This will take care of it if this is an ajax request, or a 3rd party return hit
			$new_ff = $this->session_getLastRapidHTMLForm();
			$this->addRequestData( array ( 'ffname' => $new_ff ) );

			//and debug log a little
			$this->logger->debug( "Setting form to last successful ('$new_ff')" );
		} else if ( GatewayFormChooser::isValidForm( $ffname . "-$country", $country, $currency, $payment_method, $payment_submethod, $recurring, $gateway ) ) {
			//if the country-specific version exists, use that.
			$this->addRequestData( array ( 'ffname' => $ffname . "-$country" ) );

			//I'm only doing this for serious legacy purposes. This mess needs to stop itself. To help with the mess-stopping...
			$message = "ffname '$ffname' was invalid, but the country-specific '$ffname-$country' works. utm_source = '$utm', referrer = '$ref'";
			$this->logger->warning( $message );
		} else {
			//Invalid form. Go get one that is valid, and squawk in the error logs.
			$new_ff = GatewayFormChooser::getOneValidForm( $country, $currency, $payment_method, $payment_submethod, $recurring, $gateway );
			$this->addRequestData( array ( 'ffname' => $new_ff ) );

			//now construct a useful error message
			$message = "ffname '{$ffname}' is invalid. Assigning ffname '{$new_ff}'. " .
				"I currently am choosing for: " . $this->getLogDebugJSON();

			if ( empty( $ffname ) ) {
				// Gateway-specific link didn't specify a form, but we have a
				// default. Don't squawk too loud.
				$this->logger->warning( $message );
			} else {
				$this->logger->error( $message );
			}

			//Turn these off by setting the LogDebug global to false.
			$this->logger->debug( "GET: " . json_encode( $_GET ) );
			$this->logger->debug( "POST: " . json_encode( $_POST ) );

			$dontwannalog = array (
				'user_ip',
				'server_ip',
				'descriptor',
				'account_name',
				'account_number',
				'authorization_id',
				'bank_check_digit',
				'bank_name',
				'bank_code',
				'branch_code',
				'country_code_bank',
				'date_collect',
				'direct_debit_text',
				'iban',
				'fiscal_number',
				'cvv',
			);

			foreach ( $data as $key => $val ) {
				if ( in_array( $key, $dontwannalog ) ) {
					unset( $data[$key] );
				}
			}
			$this->logger->debug( "Truncated DonationData: " . json_encode( $data ) );
		}
	}

	/**
	 * buildOrderIDSources: Uses the 'alt_locations' array in the order id
	 * metadata, to build an array of all possible candidates for order_id.
	 * This will also weed out candidates that do not meet the
	 * gateway-specific data constraints for that field, and are therefore
	 * invalid.
	 *
	 * @TODO: Data Item Class. There should be a class that keeps track of
	 * the metadata for every field we use (everything that currently comes
	 * back from DonationData), that can be overridden per gateway. Revisit
	 * this in a more universal way when that time comes.
	 */
	public function buildOrderIDSources() {
		static $built = false;

		if ( $built && isset( $this->order_id_candidates ) ) { //once per request is plenty
			return;
		}

		//pull all order ids and variants from all their usual locations
		$locations = array (
			'_GET' => 'order_id',
			'_POST' => 'order_id',
			'_SESSION' => array ( 'Donor' => 'order_id' ),
		);

		$alt_locations = $this->getOrderIDMeta( 'alt_locations' );
		if ( $alt_locations && is_array( $alt_locations ) ) {
			foreach ( $alt_locations as $var => $key ) {
				$locations[$var] = $key;
			}
		}

		//Now pull all the locations and populate the candidate array.
		$oid_candidates = array ( );

		foreach ( $locations as $var => $key ) {
			//using a horribly redundant switch here until php supports superglobals with $$. Arglebarglefargle!
			switch ( $var ) {
				case "_GET" :
					if ( array_key_exists( $key, $_GET ) ) {
						$oid_candidates[$var] = $_GET[$key];
					}
					break;
				case "_POST" :
					if ( array_key_exists( $key, $_POST ) ) {
						$oid_candidates[$var] = $_POST[$key];
					}
				case "_SESSION" :
					if ( $this->session_exists() ) {
						if ( is_array( $key ) ) {
							foreach ( $key as $subkey => $subvalue ) {
								if ( array_key_exists( $subkey, $_SESSION ) && array_key_exists( $subvalue, $_SESSION[$subkey] ) ) {
									$oid_candidates['_SESSION' . $subkey . $subvalue] = $_SESSION[$subkey][$subvalue];
								}
							}
						} else {
							if ( array_key_exists( $key, $_SESSION ) ) {
								$oid_candidates[$var] = $_SESSION[$key];
							}
						}
					}
					break;
				default :
					if ( !is_array( $key ) && array_key_exists( $key, $$var ) ) {
						//simple case first. This is a direct key in $var.
						$oid_candidates[$var] = $$var[$key];
					}
					if ( is_array( $key ) ) {
						foreach ( $key as $subkey => $subvalue ) {
							if ( array_key_exists( $subkey, $$var ) && array_key_exists( $subvalue, $$var[$subkey] ) ) {
								$oid_candidates[$var . $subkey . $subvalue] = $$var[$subkey][$subvalue];
							}
						}
					}
					break;
			}
		}

		//unset every invalid candidate
		foreach ( $oid_candidates as $source => $value ) {
			if ( empty( $value ) || !$this->validateDataConstraintsMet( 'order_id', $value ) ) {
				unset( $oid_candidates[$source] );
			}
		}

		$this->order_id_candidates = $oid_candidates;
		$built = true;
	}

	/**
	 * Validates that the gateway-specific data constraints for this field
	 * have been met.
	 * @param string $field The field name we're checking
	 * @param mixed $value The candidate value of the field we want to check
	 * @return boolean True if it's a valid value for that field, false if it isn't.
	 */
	function validateDataConstraintsMet( $field, $value ) {
		$met = true;

		if ( is_array( $this->dataConstraints ) && array_key_exists( $field, $this->dataConstraints ) ) {
			$type = $this->dataConstraints[$field]['type'];
			$length = $this->dataConstraints[$field]['length'];
			switch ( $type ) {
				case 'numeric' :
					//@TODO: Determine why the DataValidator's type validation functions are protected.
					//There is no good answer, use those.
					//In fact, we should probably just port the whole thing over there. Derp.
					if ( !is_numeric( $value ) ) {
						$met = false;
					} elseif ( $field === 'order_id' && $this->getOrderIDMeta( 'disallow_decimals' ) ) { //haaaaaack...
						//it's a numeric string, so all the number functions (like is_float) always return false. Because, string.
						if ( strpos( $value, '.' ) !== false ) {
							//we don't want decimals. Something is wrong. Regen.
							$met = false;
						}
					}
					break;
				case 'alphanumeric' :
					//TODO: Something better here.
					break;
				default:
					//fail closed.
					$met = false;
			}

			if ( strlen( $value ) > $length ) {
				$met = false;
			}
		}
		return $met;
	}

	/**
	 * This function is meant to be run by the DonationData class, both
	 * before and after any communication has been done that might retrieve
	 * an order ID.
	 * To put it another way: If we are meant to be getting the OrderID from
	 * a piece of gateway communication that hasn't been done yet, this
	 * should return NULL. I think.
	 * @param string $override The pre-determined value of order_id.
	 * When you want to normalize an order_id to something you have already
	 * sorted out (anything running in batch mode is a good candidate - you
	 * have probably grabbed a preexisting order_id from some external data
	 * source in that case), short-circuit the hunting process and just take
	 * the override's word for order_id's final value.
	 * Also used when receiving the order_id from external sources
	 * (example: An API response)
	 *
	 * @param DonationData $dataObj Reference to the donation data object when
	 * we're creating the order ID in the constructor of the object (and thus
	 * do not yet have a reference to it.)
	 * @return string The normalized value of order_id
	 */
	public function normalizeOrderID( $override = null, $dataObj = null ) {
		$selected = false;
		$source = null;
		$value = null;
		if ( !is_null( $override ) && $this->validateDataConstraintsMet( 'order_id', $override ) ) {
			//just do it.
			$selected = true;
			$source = 'override';
			$value = $override;
		} else {
			//we are not overriding. Exit if we've been here before and decided something.
			if ( $this->getOrderIDMeta( 'final' ) ) {
				return $this->getOrderIDMeta( 'final' );
			}
		}

		$this->buildOrderIDSources(); //make sure all possible preexisting data is ready to go

		//If there's anything in the candidate array, take it. It's already in default order of preference.
		if ( !$selected && is_array( $this->order_id_candidates ) && !empty( $this->order_id_candidates ) ) {
			$selected = true;
			reset( $this->order_id_candidates );
			$source = key( $this->order_id_candidates );
			$value = $this->order_id_candidates[$source];
		}

		if ( !$selected && !array_key_exists( 'generated', $this->order_id_candidates ) && $this->getOrderIDMeta( 'generate' ) ) {
			$selected = true;
			$source = 'generated';
			$value = $this->generateOrderID( $dataObj );
			$this->order_id_candidates[$source] = $value; //so we don't regen accidentally
		}

		if ( $selected ) {
			$this->setOrderIDMeta( 'final', $value );
			$this->setOrderIDMeta( 'final_source', $source );
			return $value;
		} elseif ( $this->getOrderIDMeta( 'generate' ) ) {
			//I'd dump the whole oid meta array here, but it's pretty much guaranteed to be empty if we're here at all.
			$this->logger->error( __FUNCTION__ . ": Unable to determine what oid to use, in generate mode." );
		}

		return null;
	}

	/**
	 * Default orderID generation
	 * This used to be done in DonationData, but gateways should control
	 * the format here. Override this in child classes.
	 *
	 * @param DonationData $dataObj Reference to the donation data object
	 * when we are forced to create the order ID during construction of it
	 * and thus do not already have a reference. THIS IS A HACK! /me vomits
	 *
	 * @return int A freshly generated order ID
	 */
	public function generateOrderID( $dataObj = null ) {
		if ( $this->getOrderIDMeta( 'ct_id' ) ) {
			// This option means use the contribution tracking ID with the
			// sequence number tacked on to the end for uniqueness
			$dataObj = ( $dataObj ) ?: $this->dataObj;

			$ctid = $dataObj->getVal_Escaped( 'contribution_tracking_id' );
			if ( !$ctid ) {
				$ctid = $dataObj->saveContributionTrackingData( true );
			}

			$this->session_ensure();
			$sequence = $this->session_getData( 'sequence' ) ?: 0;

			return "{$ctid}.{$sequence}";
		}
		$order_id = ( string ) mt_rand( 1000, 9999999999 );
		return $order_id;
	}

	public function regenerateOrderID() {
		$id = null;
		if ( $this->getOrderIDMeta( 'generate' ) ) {
			$id = $this->generateOrderID(); // should we pass $this->dataObj?
			$source = 'regenerated';  //This implies the try number is > 1.
			$this->order_id_candidates[$source] = $id;
			//alter the meta with the new data
			$this->setOrderIDMeta( 'final', $id );
			$this->setOrderIDMeta( 'final_source', 'regenerated' );
		} else {
			//we are not regenerating ourselves, but we need a new one...
			//so, blank it and wait.
			$this->order_id_candidates = array ( );
			unset( $this->order_id_meta['final'] );
			unset( $this->order_id_meta['final_source'] );
		}

		//tell DonationData about it
		$this->addRequestData( array ( 'order_id' => $id ) );
		return $id;
	}

	/**
	 * returns the orderID Meta
	 * @param string $key The key to retrieve. Optional.
	 * @return mixed|false Data requested, or false if it is not set.
	 */
	public function getOrderIDMeta( $key = false ) {
		$data = $this->order_id_meta;
		if ( !is_array( $data ) ) {
			return false;
		}

		if ( $key ) {
			//just return the key if it exists
			if ( array_key_exists( $key, $data ) ) {
				return $data[$key];
			}
		} else {
			return $data;
		}
	}

	/**
	 * sets more orderID Meta, so we can remember things about what we chose
	 * to go with in later logic.
	 * @param string $key The key to set.
	 * @param mixed $value The value to set.
	 */
	public function setOrderIDMeta( $key, $value ) {
		$this->order_id_meta[$key] = $value;
	}

	/**
	 * Get payment method meta
	 *
	 * @param string|null $payment_method Defaults to the current payment method, if null.
	 *
	 * @throws OutOfBoundsException
	 */
	public function getPaymentMethodMeta( $payment_method = null ) {
		if ( $payment_method === null ) {
			$payment_method = $this->getPaymentMethod();
		}

		if ( isset( $this->payment_methods[ $payment_method ] ) ) {

			return $this->payment_methods[ $payment_method ];
		}
		else {
			$message = "The payment method [{$payment_method}] was not found.";
			throw new OutOfBoundsException( $message );
		}
	}

	/**
	 * Get payment submethod meta
	 *
	 * @param    string|null    $payment_submethod    Payment submethods are mapped to paymentproductid
	 * @throws OutOfBoundsException
	 */
	public function getPaymentSubmethodMeta( $payment_submethod = null ) {
		if ( is_null( $payment_submethod ) ) {
			$payment_submethod = $this->getPaymentSubmethod();
		}

		if ( isset( $this->payment_submethods[ $payment_submethod ] ) ) {
			$this->logger->debug( 'Getting metadata for payment submethod: ' . ( string ) $payment_submethod );

			// Ensure that the validation index is set.
			if ( !isset( $this->payment_submethods[ $payment_submethod ]['validation'] ) ) {
				$this->payment_submethods[ $payment_submethod ]['validation'] = array();
			}

			return $this->payment_submethods[ $payment_submethod ];
		}
		else {
			throw new OutOfBoundsException( "The payment submethod [{$payment_submethod}] was not found." );
		}
	}

	/**
	 * Get metadata for all available submethods, given current method / country
	 * TODO: A PaymentMethod should be able to list its child options.  Probably
	 * still need some gateway-specific logic to prune the list by country and
	 * currency.
	 * TODO: Make it possible to override availability by currency and currency
	 * in LocalSettings.  Idea: same metadata array structure as used in
	 * definePaymentMethods, overrides cascade from
	 * methodMeta -> submethodMeta -> settingsMethodMeta -> settingsSubmethodMeta
	 * @return array with available submethods
	 *	'visa' => array( 'label' => 'Visa' )
	 */
	function getAvailableSubmethods() {
		$method = $this->getPaymentMethod();

		$submethods = array();
		foreach( $this->payment_submethods as $key => $available_submethod ) {
			if ( $available_submethod['group'] !== $method ) {
				continue; // skip anything not part of the selected method
			}
			if (
				$this->unstaged_data // need data for country filter
				&& isset( $available_submethod['countries'] )
				// if the list exists, the current country key needs to exist and have a true value
				&& empty( $available_submethod['countries'][$this->getData_Unstaged_Escaped( 'country' )] )
			) {
				continue; // skip 'em if they're not allowed round here
			}
			$submethods[$key] = $available_submethod;
		}
		return $submethods;
	}

	/**
	 * Returns some useful debugging JSON we can append to loglines for
	 * increaded debugging happiness.
	 * This is working pretty well for debugging FormChooser problems, so
	 * let's use it other places. Still, this should probably still be used
	 * sparingly...
	 * @return string JSON-encoded donation data
	 */
	public function getLogDebugJSON() {
		$logObj = array (
			'ffname',
			'country',
			'currency_code',
			'payment_method',
			'payment_submethod',
			'recurring',
			'gateway',
			'utm_source',
			'referrer',
		);

		foreach ( $logObj as $key => $value ) {
			$logObj[$value] = $this->getData_Unstaged_Escaped( $value );
			unset( $logObj[$key] );
		}

		return json_encode( $logObj );
	}
}
