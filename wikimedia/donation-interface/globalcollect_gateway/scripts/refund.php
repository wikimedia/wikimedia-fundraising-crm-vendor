<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../../..';
}

//If you get errors on this next line, set (and export) your MW_INSTALL_PATH var. 
require_once( "$IP/maintenance/Maintenance.php" );

// Refunds credit card transactions listed in a file.
// Currently takes a CSV with no header and columns in this order:
// order_id, merchant_reference, effort_id, payment_submethod, country, currency_code, amount
class GlobalCollectRefundMaintenance extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->addOption( 'file', 'Read refund detail in from a file',
			true, true, 'f' );
		$this->addOption( 'unsubscribe', 'Cancel the subscription this charge is a part of',
			false, false );
	}

	public function execute() {
		global $wgGlobalCollectGatewayEnableCustomFilters;

		// don't run fraud checks for refunds
		$wgGlobalCollectGatewayEnableCustomFilters = false;

		$isUnsubscribing = $this->getOption( 'unsubscribe' );

		$filename = $this->getOption( 'file' );
		if( !( $file = fopen( $filename, 'r' ) ) ){
			$this->error( 'Could not find refund file: ' . $filename, true );
        }
		while ( $refund = fgetcsv( $file ) ) {
			if ( count( $refund ) !== 7 ) {
				$this->error( 'Refund lines must have exactly 7 fields', true );
			}
			$oid = $refund[0];
			$effort_id = $refund[2];
			$gateway_opts = array(
				'batch_mode' => true,
				'external_data' => array(
					'order_id' => $oid,
					'merchant_reference' => $refund[1],
					'effort_id' => $effort_id,
					'payment_method' => 'cc',
					'payment_submethod' => $refund[3],
					'country' => $refund[4],
					'currency_code' => $refund[5],
					'amount' => $refund[6],
				),
			);

			$this->output( "Refunding transaction $oid\n" );
			$adapter = new GlobalCollectAdapter( $gateway_opts );
			// FIXME: effort_id is clobbered in setGatewayDefaults
			$adapter->addRequestData( array( 'effort_id' => $effort_id ) );
			$result = $adapter->doRefund();

			if ( $result->isFailed() ) {
				$this->error( "Failed refunding transaction $oid" );
			} else {
				$this->output( "Successfully refunded transaction $oid\n" );
			}

			if ( $isUnsubscribing ) {
				$result = $adapter->cancelSubscription();

				if ( $result->isFailed() ) {
					$this->error( "Failed cancelling subscription $oid" );
				} else {
					$this->output( "Successfully cancelled subscription $oid\n" );
				}
			}
		}
		fclose( $file );
	}
}

$maintClass = 'GlobalCollectRefundMaintenance';
require_once RUN_MAINTENANCE_IF_MAIN;
