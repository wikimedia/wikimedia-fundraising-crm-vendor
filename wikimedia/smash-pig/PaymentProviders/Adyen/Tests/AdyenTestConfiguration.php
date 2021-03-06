<?php namespace SmashPig\PaymentProviders\Adyen\Tests;

use SmashPig\Core\GlobalConfiguration;
use SmashPig\Tests\TestingProviderConfiguration;

class AdyenTestConfiguration extends TestingProviderConfiguration {

	public static function instance( $mockApi, GlobalConfiguration $globalConfig ) {
		$config = static::createForProvider( 'adyen', $globalConfig );
		$config->objects['api'] = $mockApi;
		return $config;
	}

	public static function getSuccessfulApproveResult() {
		return (object)[ 'captureResult' => (object)[
			'response' => '[capture-received]',
			'pspReference' => '00000000000000AB'
		] ];
	}

	public static function getSuccessfulCancelResult() {
		return (object)[ 'cancelResult' => (object)[
			'response' => '[cancel-received]',
			'pspReference' => '00000000000000AB'
		] ];
	}
}
