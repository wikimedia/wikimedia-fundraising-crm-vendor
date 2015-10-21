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
 * WorldpayGateway
 *
 */
class WorldpayGateway extends GatewayPage {

	/**
	 * Constructor - set up the new special page
	 */
	public function __construct() {
		$this->adapter = new WorldpayAdapter();
		parent::__construct();
	}

	/**
	 * Show the special page
	 *
	 * @todo
	 * - Finish error handling
	 */
	protected function handleRequest() {
		if ( $this->adapter->isESOP() ) {
			$this->getOutput()->addModules( 'ext.donationinterface.worldpay.esopjs' );
		} else {
			$this->getOutput()->addModules( 'ext.donationinterface.worldpay.styles' ); //loads early
			$this->getOutput()->addModules( 'ext.donationinterface.worldpay.code' ); //loads at normal time
		}

		$this->handleDonationRequest();
	}

	protected function isProcessImmediate() {
		// FIXME: should be checked by the adapter rather than looking at the request.
		return $this->getRequest()->getText( 'OTT' );
	}
}
