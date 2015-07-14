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

class PaypalGateway extends GatewayPage {

	/**
	 * Constructor - set up the new special page
	 */
	public function __construct() {
		$this->adapter = new PaypalAdapter();
		parent::__construct(); //the next layer up will know who we are.
	}

	/**
	 * Show the special page
	 */
	protected function handleRequest() {
		$this->getOutput()->allowClickjacking();

		$this->handleDonationRequest();
	}

	/**
	 * Always attempt to pass through transparently.
	 *
	 * @see GatewayPage::isProcessImmediate()
	 */
	protected function isProcessImmediate() {
		return true;
	}
}
