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
 * GatewayPage
 * This class is the generic unlisted special page in charge of actually 
 * displaying the form. Each gateway will have one or more direct descendants of 
 * this class, with most of the gateway-specific control logic in its handleRequest
 * function. For instance: extensions/DonationInterface/globalcollect_gateway/globalcollect_gateway.body.php
 *
 */
abstract class GatewayPage extends UnlistedSpecialPage {
	/**
	 * An array of form errors
	 * @var array $errors
	 */
	public $errors = array( );

	/**
	 * The gateway adapter object
	 * @var GatewayAdapter $adapter
	 */
	public $adapter;

	/**
	 * Gateway-specific logger
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->logger = DonationLoggerFactory::getLogger( $this->adapter );
		$this->getOutput()->addModules( 'donationInterface.skinOverride' );
		
		$me = get_called_class();
		parent::__construct( $me );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgContributionTrackingFundraiserMaintenance, $wgContributionTrackingFundraiserMaintenanceUnsched;

		// FIXME: Deprecate "language" param.
		$language = $this->getRequest()->getVal( 'language' );
		if ( $language ) {
			RequestContext::getMain()->setLanguage( $language );
			global $wgLang;
			$wgLang = RequestContext::getMain()->getLanguage();
		}

		if ( $this->adapter->getGlobal( 'Enabled' ) !== true ) {
			$this->displayFailPage();
			return;
		}

		if( $wgContributionTrackingFundraiserMaintenance
			|| $wgContributionTrackingFundraiserMaintenanceUnsched ){
			$this->getOutput()->redirect( Title::newFromText('Special:FundraiserMaintenance')->getFullURL(), '302' );
			return;
		}

		try {
			$this->handleRequest();
		} catch ( Exception $ex ) {
			$this->logger->error( "Gateway page errored out due to: " . $ex->getMessage() );
			$this->displayFailPage();
		}
	}

	/**
	 * Should be overridden in each derived class to actually handle the request
	 * Performs gateway-specific checks and either redirects or displays form.
	 */
	protected abstract function handleRequest();

	/**
	 * Checks current dataset for validation errors
	 * TODO: As with every other bit of gateway-related logic that should 
	 * definitely be available to every entry point, and functionally has very 
	 * little to do with being contained within what in an ideal world would be 
	 * a piece of mostly UI, this function needs to be moved inside the gateway 
	 * adapter class.
	 *
	 * @return boolean Returns false on an error-free validation, otherwise true.
	 * FIXME: that return value seems backwards to me.
	 */
	public function validateForm() {

		$validated_ok = $this->adapter->revalidate();

		if ( !$validated_ok ) {
			if ( $this->fallbackToDefaultCurrency() ) {
				$validated_ok = $this->adapter->revalidate();
				$notify = $this->adapter->getGlobal( 'NotifyOnConvert' );

				if ( $notify || !$validated_ok ) {
					$this->adapter->addManualError( array(
						'general' => $this->msg( 'donate_interface-fallback-currency-notice', 
												 $this->adapter->getGlobal( 'FallbackCurrency' ) )->text()
					) );
					$validated_ok = false;
				}
			}
		}

		return !$validated_ok;
	}

	/**
	 * Build and display form to user
	 */
	public function displayForm() {
		global $wgOut;

		$form_class = $this->getFormClass();
		// TODO: use interface.  static ctor.
		if ( $form_class && class_exists( $form_class ) ){
			$form_obj = new $form_class( $this->adapter );
			$form = $form_obj->getForm();
			$wgOut->addModules( $form_obj->getResources() );
			$wgOut->addHTML( $form );
		} else {
			$this->displayFailPage();
		}
	}

	/**
	 * Display a generic failure page
	 */
	public function displayFailPage() {
		global $wgOut;

		$page = $this->adapter->getFailPage();

		$log_message = "Redirecting to [{$page}]";
		$this->logger->info( $log_message );

		$wgOut->redirect( $page );
	}

	/**
	 * Get the currently set form class
	 * @return mixed string containing the valid and enabled form class, otherwise false. 
	 */
	public function getFormClass() {
		return $this->adapter->getFormClass();
	}

	/**
	 * displayResultsForDebug
	 *
	 * Displays useful information for debugging purposes.
	 * Enable with $wgDonationInterfaceDisplayDebug, or the adapter equivalent.
	 * @param PaymentTransactionResponse $results
	 * @return null
	 */
	protected function displayResultsForDebug( PaymentTransactionResponse $results = null ) {
		global $wgOut;
		
		$results = empty( $results ) ? $this->adapter->getTransactionResponse() : $results;
		
		if ( $this->adapter->getGlobal( 'DisplayDebug' ) !== true ){
			return;
		}
		$wgOut->addHTML( HTML::element( 'span', null, $results->getMessage() ) );

		$errors = $results->getErrors();
		if ( !empty( $errors ) ) {
			$wgOut->addHTML( HTML::openElement( 'ul' ) );
			foreach ( $errors as $code => $value ) {
				$wgOut->addHTML( HTML::element('li', null, "Error $code: " . print_r( $value, true ) ) );
			}
			$wgOut->addHTML( HTML::closeElement( 'ul' ) );
		}

		$data = $results->getData();
		if ( !empty( $data ) ) {
			$wgOut->addHTML( HTML::openElement( 'ul' ) );
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$wgOut->addHTML( HTML::openElement('li', null, $key ) . HTML::openElement( 'ul' ) );
					foreach ( $value as $key2 => $val2 ) {
						$wgOut->addHTML( HTML::element('li', null, "$key2: $val2" ) );
					}
					$wgOut->addHTML( HTML::closeElement( 'ul' ) . HTML::closeElement( 'li' ) );
				} else {
					$wgOut->addHTML( HTML::element('li', null, "$key: $value" ) );
				}
			}
			$wgOut->addHTML( HTML::closeElement( 'ul' ) );
		} else {
			$wgOut->addHTML( "Empty Results" );
		}
		if ( array_key_exists( 'Donor', $_SESSION ) ) {
			$wgOut->addHTML( "Session Donor Vars:" . HTML::openElement( 'ul' ));
			foreach ( $_SESSION['Donor'] as $key => $val ) {
				$wgOut->addHTML( HTML::element('li', null, "$key: $val" ) );
			}
			$wgOut->addHTML( HTML::closeElement( 'ul' ) );
		} else {
			$wgOut->addHTML( "No Session Donor Vars:" );
		}

		if ( is_array( $this->adapter->debugarray ) ) {
			$wgOut->addHTML( "Debug Array:" . HTML::openElement( 'ul' ) );
			foreach ( $this->adapter->debugarray as $val ) {
				$wgOut->addHTML( HTML::element('li', null, $val ) );
			}
			$wgOut->addHTML( HTML::closeElement( 'ul' ) );
		} else {
			$wgOut->addHTML( "No Debug Array" );
		}
	}

	/**
	 * Fetch the array of iso country codes => country names
	 * @return array
	 */
	public static function getCountries() {
		return CountryCodes::getCountryCodes();
	}

	/**
	 * If a currency code error exists and fallback currency conversion is 
	 * enabled for this adapter, convert intended amount to default currency.
	 *
	 * @return boolean whether currency conversion was performed
	 * @throws DomainException
	 */
	protected function fallbackToDefaultCurrency() {
		$defaultCurrency = $this->adapter->getGlobal( 'FallbackCurrency' );
		if ( !$defaultCurrency ) {
			return false;
		}
		$form_errors = $this->adapter->getValidationErrors();
		if ( !$form_errors || !array_key_exists( 'currency_code', $form_errors ) ) {
			return false;
		}
		// If the currency is invalid, fallback to default.
		// Our conversion rates are all relative to USD, so use that as an
		// intermediate currency if converting between two others.
		$oldCurrency = $this->adapter->getData_Unstaged_Escaped( 'currency_code' );
		if ( $oldCurrency === $defaultCurrency ) {
			$adapterClass = $this->adapter->getGatewayAdapterClass();
			throw new DomainException( __FUNCTION__ . " Unsupported currency $defaultCurrency set as fallback for $adapterClass." );
		}
		$oldAmount = $this->adapter->getData_Unstaged_Escaped( 'amount' );
		$usdAmount = 0.0;
		$newAmount = 0;

		$conversionRates = CurrencyRates::getCurrencyRates();
		if ( $oldCurrency === 'USD' ) {
			$usdAmount = $oldAmount;
		}
		elseif ( array_key_exists( $oldCurrency, $conversionRates ) ) {
			$usdAmount = $oldAmount / $conversionRates[$oldCurrency];
		}
		else {
			// We can't convert from this unknown currency.
			return false;
		}

		if ( $defaultCurrency === 'USD' ) {
			$newAmount = floor( $usdAmount );
		}
		elseif ( array_key_exists( $defaultCurrency, $conversionRates ) ) {
			$newAmount = floor( $usdAmount * $conversionRates[$defaultCurrency] );
		}

		$this->adapter->addRequestData( array(
			'amount' => $newAmount,
			'currency_code' => $defaultCurrency
		) );

		$this->logger->info( "Unsupported currency $oldCurrency forced to $defaultCurrency" );
		return true;
	}

	/**
	 * Respond to a donation request
	 */
	protected function handleDonationRequest() {
		$this->setHeaders();

		// TODO: this is where we should feed GPCS parameters into DonationData.

		// dispatch forms/handling
		if ( $this->adapter->checkTokens() ) {
			if ( $this->isProcessImmediate() ) {
				// Check form for errors
				// FIXME: Should this be rolled into adapter.doPayment?
				$form_errors = $this->validateForm();

				// If there were errors, redisplay form, otherwise proceed to next step
				if ( $form_errors ) {
					$this->displayForm();
				} else {
					// Attempt to process the payment, and render the response.
					$this->processPayment();
				}
			} else {
				$this->adapter->session_addDonorData();
				$this->displayForm();
			}
		} else { //token mismatch
			$error['general']['token-mismatch'] = $this->msg( 'donate_interface-token-mismatch' );
			$this->adapter->addManualError( $error );
			$this->displayForm();
		}
	}

	/**
	 * Determine if we should attempt to process the payment now
	 *
	 * @return bool True if we should attempt processing.
	 */
	protected function isProcessImmediate() {
		// If the user posted to this form, process immediately.
		if ( $this->adapter->posted ) {
			return true;
		}

		// Otherwise, respect the "redirect" parameter.  If it is "1", try to
		// skip the interstitial page.  If it's "0", do not process immediately.
		$redirect = $this->adapter->getData_Unstaged_Escaped( 'redirect' );
		if ( $redirect !== null ) {
			return ( $redirect === '1' || $redirect === 'true' );
		}

		return false;
	}

	/**
	 * Whether or not the user comes back to the resultswitcher in an iframe
	 * @return boolean true if we need to pop out of an iframe, otherwise false
	 */
	protected function isReturnFramed() {
		return false;
	}

	/**
	 * Render a resultswitcher page
	 */
	protected function handleResultRequest() {
		//no longer letting people in without these things. If this is
		//preventing you from doing something, you almost certainly want to be
		//somewhere else.
		$forbidden = false;
		if ( !$this->adapter->session_hasDonorData() ) {
			$forbidden = true;
			$f_message = 'No active donation in the session';
		}

		if ( $forbidden ){
			wfHttpError( 403, 'Forbidden', wfMsg( 'donate_interface-error-http-403' ) );
		}
		$oid = $this->adapter->getData_Unstaged_Escaped( 'order_id' );

		$referrer = $this->getRequest()->getHeader( 'referer' );
		$liberated = false;
		if ( $this->adapter->session_getData( 'order_status', $oid ) === 'liberated' ) {
			$liberated = true;
		}

		// XXX need to know whether we were in an iframe or not.
		global $wgServer;
		if ( $this->isReturnFramed() && ( strpos( $referrer, $wgServer ) === false ) && !$liberated ) {
			$_SESSION[ 'order_status' ][ $oid ] = 'liberated';
			$this->logger->info( "Resultswitcher: Popping out of iframe for Order ID " . $oid );
			//TODO: Move the $forbidden check back to the beginning of this if block, once we know this doesn't happen a lot.
			//TODO: If we get a lot of these messages, we need to redirect to something more friendly than FORBIDDEN, RAR RAR RAR.
			if ( $forbidden ) {
				$this->logger->error( "Resultswitcher: $oid SHOULD BE FORBIDDEN. Reason: $f_message" );
			}
			$this->getOutput()->allowClickjacking();
			$this->getOutput()->addModules( 'iframe.liberator' );
			return;
		}

		$this->setHeaders();

		if ( $forbidden ){
			throw new RuntimeException( "Resultswitcher: Request forbidden. " . $f_message . " Adapter Order ID: $oid" );
		}
		$this->logger->info( "Resultswitcher: OK to process Order ID: " . $oid );

		if ( $this->adapter->checkTokens() ) {
			$this->getOutput()->allowClickjacking();
			// FIXME: do we really need this again?
			$this->getOutput()->addModules( 'iframe.liberator' );
			// processResponse expects some data, so let's feed it all the
			// GET and POST vars
			$response = $this->getRequest()->getValues();
			// TODO: run the whole set of getResponseStatus, getResponseErrors
			// and getResponseData first.  Maybe do_transaction with a
			// communication_type of 'incoming' and a way to provide the
			// adapter the GET/POST params harvested here.
			$this->adapter->processResponse( $response );
			switch ( $this->adapter->getFinalStatus() ) {
			case FinalStatus::COMPLETE:
			case FinalStatus::PENDING:
				$this->getOutput()->redirect( $this->adapter->getThankYouPage() );
				return;
			}
		} else {
			$this->logger->error( "Resultswitcher: Token Check Failed. Order ID: $oid" );
		}
		$this->displayFailPage();
	}

	/**
	 * Ask the adapter to perform a payment
	 *
	 * Route the donor based on the response.
	 */
	protected function processPayment() {
		$this->renderResponse( $this->adapter->doPayment() );
	}

	/**
	 * Take UI action suggested by the payment result
	 */
	protected function renderResponse( PaymentResult $result ) {
		if ( $result->isFailed() ) {
			$this->displayFailPage();
		} elseif ( $url = $result->getRedirect() ) {
			$this->getOutput()->redirect( $url );
		} elseif ( $url = $result->getIframe() ) {
			// Show a form containing an iframe.

			// Well, that's sketchy.  See TODO in renderIframe: we should
			// accomplish this entirely by passing an iframeSrcUrl parameter
			// to the template.
			$this->displayForm();

			$this->renderIframe( $url );
		} elseif ( $form = $result->getForm() ) {
			// Show another form.

			$this->adapter->addRequestData( array(
				'ffname' => $form,
			) );
			$this->displayForm();
		} elseif ( $errors = $result->getErrors() ) {
			// FIXME: Creepy.  Currently, the form inspects adapter errors.  Use
			// the stuff encapsulated in PaymentResult instead.
			foreach ( $this->adapter->getTransactionResponse()->getErrors() as $code => $transactionError ) {
				$message = $transactionError['message'];
				$error = array();
				if ( !empty( $transactionError['context'] ) ) {
					$error[$transactionError['context']] = $message;
				} else if ( strpos( $code, 'internal' ) === 0 ) {
					$error['retryMsg'][ $code ] = $message;
				}
				else {
					$error['general'][ $code ] = $message;
				}
				$this->adapter->addManualError( $error );
			}
			$this->displayForm();
		} else {
			// Success.
			$this->getOutput()->redirect( $this->adapter->getThankYouPage() );
		}
	}

	/**
	 * Append iframe
	 *
	 * TODO: Should be rendered by the template.
	 *
	 * @param string $url
	 */
	protected function renderIframe( $url ) {
		$attrs = array(
			'id' => 'paymentiframe',
			'name' => 'paymentiframe',
			'width' => '680',
			'height' => '300'
		);

		$attrs['frameborder'] = '0';
		$attrs['style'] = 'display:block;';
		$attrs['src'] = $url;
		$paymentFrame = Xml::openElement( 'iframe', $attrs );
		$paymentFrame .= Xml::closeElement( 'iframe' );

		$this->getOutput()->addHTML( $paymentFrame );
	}
}
