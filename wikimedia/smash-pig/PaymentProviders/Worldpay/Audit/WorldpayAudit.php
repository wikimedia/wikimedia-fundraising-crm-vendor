<?php

namespace SmashPig\PaymentProviders\Worldpay\Audit;

class WorldpayAudit {

	// FIXME this never gets called and refers to a non-existent class
	function retrieveFiles() {
		AuditRetriever::retrieveAll( 'worldpay' );
	}

	/**
	 * @param string $file Full path to audit file.
	 * @return array List of normalized messages, or empty if the file couldn't be parsed.
	 * @throws Exception If an error interrupts processing.
	 */
	function parseFile( $file ) {
		// FIXME: this should be specified in configuration
		$fileTypes = array(
			'SmashPig\PaymentProviders\Worldpay\Audit\TransactionReconciliationFile',
			// FIXME: Disabled due to brokenness.
			//'SmashPig\PaymentProviders\Worldpay\Audit\LynkReconciliationFile',
			'SmashPig\PaymentProviders\Worldpay\Audit\WpgReconciliationFile',
		);

		$data = array();
		foreach ( $fileTypes as $type ) {
			if ( $type::isMine( $file ) ) {
				$parser = new $type();
				$data = $parser->parse( $file );
			}
		}

		return $data;
	}

}
