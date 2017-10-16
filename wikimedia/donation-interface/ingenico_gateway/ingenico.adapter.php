<?php

use Psr\Log\LogLevel;
use SmashPig\PaymentProviders\PaymentProviderFactory;

class IngenicoAdapter extends GlobalCollectAdapter {
	const GATEWAY_NAME = 'Ingenico';
	const IDENTIFIER = 'ingenico';
	const GLOBAL_PREFIX = 'wgIngenicoGateway';

	public function getCommunicationType() {
		return 'array';
	}

	public function getResponseType() {
		return 'json';
	}

	public function defineTransactions() {
		parent::defineTransactions();
		$this->transactions['createHostedCheckout'] = array(
			'request' => array(
				'hostedCheckoutSpecificInput' => array(
					'isRecurring',
					'locale',
					'paymentProductFilters' => array(
						'restrictTo' => array(
							'products' => array(
								// HACK! this array should be a simple
								// list of payment ids, not an associative array
								// so... use 'null' to flag that?
								'paymentProductId' => null
							)
						)
					),
					'returnUrl',
					'showResultPage',
					// 'tokens', // we don't store user accounts or tokens here
					// 'variant', // For a/b testing of iframe
				),
				'order' => array(
					'amountOfMoney' => array(
						'amount',
						'currencyCode',
					),
					'customer' => array(
						'billingAddress' => array(
							'city',
							'countryCode',
							// 'houseNumber' // hmm, hope this isn't used for fraud detection!
							'state',
							// 'stateCode', // should we use this instead?
							'street',
							'zip',
						),
						'contactDetails' => array(
							'emailAddress'
						),
						// 'fiscalNumber' // only required for boletos & Brazil paypal
						'locale', // used for redirection to 3rd parties
						'personalInformation' => array(
							'name' => array(
								'firstName',
								'surname',
							)
						)
					),
					/*'items' => array(
						array(
							'amountOfMoney' => array(
								'amount',
								'currencyCode',
							),
							'invoiceData' => array(
								'description'
							)
						)
					),*/
					'references' => array(
						'descriptor', // First 22+ chars appear on card statement
						'merchantReference', // unique, string(30)
					)
				)
			),
			'values' => array(
				'returnUrl' => $returnTitle = Title::newFromText( 'Special:IngenicoGatewayResult' )
					->getFullURL( false, false, PROTO_CURRENT ),
				'showResultPage' => 'false',
				'descriptor' => WmfFramework::formatMessage( 'donate_interface-donation-description' ),
			),
			'response' => array(
				'hostedCheckoutId'
			)
		);

		$this->transactions['getHostedPaymentStatus'] = array(
			'request' => array( 'hostedCheckoutId' ),
			'response' => array(
				'id',
				'amount',
				'currencyCode',
				'avsResult',
				'cvvResult',
				'statusCode',
			)
		);

		$this->transactions['getPaymentStatus'] = array(
			'request' => array( 'id' ),
			'response' => array(
				'amount',
				'currencyCode',
				'avsResult',
				'cvvResult',
				'statusCode',
			)
		);

		$this->transactions['approvePayment'] = array(
			'request' => array( 'id' ),
			'response' => array( 'statusCode' )
		);
	}

	/**
	 * Sets up the $order_id_meta array.
	 * Should contain the following keys/values:
	 * 'alt_locations' => array( $dataset_name, $dataset_key ) //ordered
	 * 'type' => numeric, or alphanumeric
	 * 'length' => $max_charlen
	 */
	public function defineOrderIDMeta() {
		$this->order_id_meta = array(
			'alt_locations' => array(),
			'ct_id' => true,
			'generate' => true,
		);
	}

	/**
	 * Make an API call to Ingenico Connect.
	 *
	 * @param array $data parameters for the transaction
	 * @return bool whether the API call succeeded
	 */
	public function curl_transaction( $data ) {
		$email = $this->getData_Unstaged_Escaped( 'email' );
		$this->logger->info( "Making API call for donor $email" );

		$filterResult = $this->runSessionVelocityFilter();
		if ( $filterResult === false ) {
			return false;
		}

		$provider = $this->getPaymentProvider();
		switch ( $this->getCurrentTransaction() ) {
			case 'createHostedCheckout':
				$result = $provider->createHostedPayment( $data );
				break;
			case 'getHostedPaymentStatus':
				$result = $provider->getHostedPaymentStatus(
					$data['hostedCheckoutId']
				);
				break;
			case 'approvePayment':
				$id = $data['id'];
				unset( $data['id'] );
				$result = $provider->approvePayment( $id, $data );
				break;
			default:
				return false;
		}

		$this->transaction_response->setRawResponse( json_encode( $result ) );
		return true;
	}

	public function getBasedir() {
		return __DIR__;
	}

	public function do_transaction( $transaction ) {
		$this->ensureUniqueOrderID();
		if ( $transaction === 'createHostedCheckout' ) {
			$this->incrementSequenceNumber();
		}
		$result = parent::do_transaction( $transaction );
		// Add things to session which may have been retrieved from API
		$this->session_addDonorData();
		return $result;
	}

	protected function getPaymentProvider() {
		$method = $this->getData_Unstaged_Escaped( 'payment_method' );
		return PaymentProviderFactory::getProviderForMethod( $method );
	}

	public function parseResponseCommunicationStatus( $response ) {
		return true;
	}

	public function parseResponseErrors( $response ) {
		$errors = array();
		if ( !empty( $response['errors'] ) ) {
			foreach ( $response['errors'] as $error ) {
				$errors[] = new PaymentError(
					$error['code'],
					$error['message'],
					LogLevel::ERROR
				);
			}
		}
		return $errors;
	}

	public function parseResponseData( $response ) {
		// Flatten the whole darn nested thing.
		// FIXME: This should probably happen in the SmashPig library where
		// we can flatten in a custom way per transaction type. Or we should
		// expand var_map to work with nested stuff.
		$flattened = array();
		$squashMe = function ( $sourceData, $squashMe ) use ( &$flattened ) {
			foreach ( $sourceData as $key => $value ) {
				if ( is_array( $value ) ) {
					call_user_func( $squashMe, $value, $squashMe );
				} else {
					// Hmm, we might be clobbering something
					$flattened[$key] = $value;
				}
			}
		};
		$squashMe( $response, $squashMe );
		if ( isset( $flattened['partialRedirectUrl'] ) ) {
			$provider = $this->getPaymentProvider();
			$flattened['FORMACTION'] = $provider->getHostedPaymentUrl(
				$flattened['partialRedirectUrl']
			);
		}
		return $flattened;
	}

	public function processDonorReturn( $requestValues ) {
		// FIXME: make sure we're processing the order ID we expect!

		$response = $this->do_transaction( 'Confirm_CreditCard' );
		return PaymentResult::fromResults(
			$response,
			$this->getFinalStatus()
		);
	}

	protected function getOrderStatusFromProcessor() {
		// FIXME: sometimes we should use getPayment
		return $this->do_transaction( 'getHostedPaymentStatus' );
	}

	protected function post_process_getHostedPaymentStatus() {
		return parent::post_process_get_orderstatus();
	}

	protected function setGatewayTransactionId() {
		// FIXME: See 'Silly' comment in PayPal Express adapter
		$this->transaction_response->setGatewayTransactionId(
			$this->getData_Unstaged_Escaped( 'gateway_txn_id' )
		);
	}

	protected function approvePayment() {
		return $this->do_transaction( 'approvePayment' );
	}

	protected function getStatusCode( $txnData ) {
		return $this->getData_Unstaged_Escaped( 'gateway_status' );
	}
}
