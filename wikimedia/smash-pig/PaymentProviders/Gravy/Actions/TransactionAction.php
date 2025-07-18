<?php

namespace SmashPig\PaymentProviders\Gravy\Actions;

use SmashPig\Core\Context;
use SmashPig\Core\DataStores\QueueWrapper;
use SmashPig\Core\Logging\TaggedLogger;
use SmashPig\Core\Messages\ListenerMessage;
use SmashPig\PaymentData\FinalStatus;
use SmashPig\PaymentProviders\Gravy\ExpatriatedMessages\TransactionMessage;
use SmashPig\PaymentProviders\Gravy\Jobs\ProcessCaptureRequestJob;
use SmashPig\PaymentProviders\Gravy\Jobs\RecordCaptureJob;
use SmashPig\PaymentProviders\Gravy\TransactionDetailsNormalizer;
use SmashPig\PaymentProviders\Responses\PaymentProviderExtendedResponse;

class TransactionAction extends GravyAction {
	use RefundTrait;

	public function execute( ListenerMessage $msg ): bool {
		$tl = new TaggedLogger( 'TransactionAction' );
		$transactionDetails = $this->getTransactionDetails( $msg );

		if ( $transactionDetails->isSuccessful() ) {
			if ( $transactionDetails->getStatus() == FinalStatus::COMPLETE ) {
				$tl->info(
					"Adding successful capture job for {$transactionDetails->getCurrency()} {$transactionDetails->getAmount()} with psp reference {$transactionDetails->getGatewayTxnId()}."
				);
				$recordCaptureJob = RecordCaptureJob::factory( $msg, $transactionDetails );
				QueueWrapper::push( $msg->getDestinationQueue(), $recordCaptureJob );
			} elseif ( $transactionDetails->getStatus() == FinalStatus::PENDING_POKE ) {
				$providerConfig = Context::get()->getProviderConfiguration();
				if ( !$providerConfig->val( 'capture-from-ipn-listener' ) ) {
					return true;
				}
				$tl->info(
					"Adding successful authorized job for {$transactionDetails->getCurrency()} {$transactionDetails->getAmount()} with psp reference {$transactionDetails->getGatewayTxnId()}"
				);
				$captureRequestJob = ProcessCaptureRequestJob::factory( $msg, $transactionDetails );
				QueueWrapper::push( $msg->getDestinationQueue(), $captureRequestJob );
			} else {
				$tl->info(
					"Received successful transaction with unknown status {$transactionDetails->getStatus()} and transaction id {$transactionDetails->getGatewayTxnId()}"
				);
			}
		} else {
			$id = $transactionDetails->getRawResponse()['id'] ?? null;
			$message = 'Skipping unsuccessful transaction';
			if ( !empty( $id ) ) {
				if ( $this->requiresChargeback( $transactionDetails ) ) {
					$message = "Pushing failed trustly transaction with id: {$id} to refund queue for chargeback.";
					$this->pushFailedAuthAsChargebackToRefundQueue( strtotime( $msg->getMessageDate() ), $transactionDetails );
				} else {
					$message = "Skipping unsuccessful transaction with transaction id {$id}";
				}
			}
			$tl->info( $message );
		}

		return true;
	}

	public function getTransactionDetails( TransactionMessage $msg ): PaymentProviderExtendedResponse {
		$transactionDetailsNormalizer = new TransactionDetailsNormalizer();
		$paymentMethod = $msg->getTransactionPaymentMethod();
		$transactionDetails = $msg->getTransactionDetails();

		return $transactionDetailsNormalizer->normalizeTransactionDetails(
			$paymentMethod,
			$transactionDetails
		);
	}

	/**
	 * Some payment method requires a chargeback message when it fails
	 * because they are set to complete status before getting a successful response
	 * @param PaymentProviderExtendedResponse $transaction
	 * @return bool
	 */
	public function requiresChargeback( PaymentProviderExtendedResponse $transaction ): bool {
		$normalizedResponse = $transaction->getNormalizedResponse();
		return isset( $normalizedResponse['type'] ) && $normalizedResponse['type'] == "chargeback";
	}

	/**
	 * Cancel saved contributions in civi using a chargeback
	 * @param string $ipnMessageDate
	 * @param \SmashPig\PaymentProviders\Responses\PaymentProviderExtendedResponse $transaction
	 * @return void
	 */
	public function pushFailedAuthAsChargebackToRefundQueue( string $ipnMessageDate, PaymentProviderExtendedResponse $transaction ) {
		$refundMessage = $this->buildRefundQueueMessage( $ipnMessageDate, $transaction->getNormalizedResponse() );
		$refundMessage['status'] = FinalStatus::COMPLETE;
		QueueWrapper::push( 'refund', $refundMessage );
	}
}
