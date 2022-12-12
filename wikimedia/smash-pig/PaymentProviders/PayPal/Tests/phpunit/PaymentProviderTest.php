<?php

namespace SmashPig\PaymentProviders\PayPal\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use SmashPig\Core\Context;
use SmashPig\Core\ProviderConfiguration;
use SmashPig\Tests\BaseSmashPigUnitTestCase;
use SmashPig\Tests\TestingProviderConfiguration;

/**
 * @group PayPal
 */
class PaymentProviderTest extends BaseSmashPigUnitTestCase {

	/**
	 * @var ProviderConfiguration
	 */
	protected $config;

	/**
	 * @var MockObject
	 */
	protected $api;

	/**
	 * @var \SmashPig\PaymentProviders\PayPal\PaymentProvider
	 */
	protected $provider;

	public function setUp(): void {
		parent::setUp();
		$ctx = Context::get();
		$this->api = $this->createMock( 'SmashPig\PaymentProviders\PayPal\Api' );
		$this->config = TestingProviderConfiguration::createForProvider( 'paypal', $ctx->getGlobalConfiguration() );
		$this->config->overrideObjectInstance( 'api', $this->api );
		$ctx->setProviderConfiguration( $this->config );
		$this->provider = $this->config->object( 'payment-provider/paypal' );
	}

	public function testGetLatestPaymentStatus() {
		// set up expectations
		$testParams = [
			'token' => 'EC-TESTTOKEN12345678910'
		];
		$testApiResponse = $this->getTestData( 'GetLatestPaymentStatus.response' );
		parse_str( $testApiResponse, $parsedTestApiResponse );

		$this->api->expects( $this->once() )
			->method( 'getExpressCheckoutDetails' )
			->with( $this->equalTo( $testParams['token'] ) )
			->willReturn( $parsedTestApiResponse );

		// call the code
		$response = $this->provider->getLatestPaymentStatus( $testParams );

		// check the results
		$this->assertInstanceOf( 'SmashPig\PaymentProviders\Responses\PaymentDetailResponse', $response );
		$this->assertTrue( $response->isSuccessful() );
		$this->assertEquals( "PaymentActionNotInitiated", $response->getRawStatus() );
		$this->assertEquals( "Success", $response->getRawResponse()['ACK'] );
	}

	public function testCreateRecurringPaymentsProfileSampleApiCall() {
		// perform the test
		$testParams = [
			'order_id' => '15190.1',
			'amount' => '30.0',
			'currency' => 'USD',
			'email' => 'test_user@paypal.com',
			'payment_token' => 'EC-74C37985WY171780F',
		];

		$testApiResponse = $this->getTestData( 'CreateRecurringPaymentsProfile.response' );
		parse_str( $testApiResponse, $parsedTestApiResponse );

		$this->api->expects( $this->once() )
			->method( 'createRecurringPaymentsProfile' )
			->with( $this->equalTo( $testParams ) )
			->willReturn( $parsedTestApiResponse );

		// call the code
		$response = $this->provider->createRecurringPaymentsProfile( $testParams );
		// check the results
		$this->assertInstanceOf( 'SmashPig\PaymentProviders\Responses\CreateRecurringPaymentsProfileResponse', $response );
		$this->assertTrue( $response->isSuccessful() );
		$this->assertEquals( "complete", $response->getStatus() );
		$this->assertEquals( "Success", $response->getRawStatus() );
		$this->assertEquals( "ActiveProfile", $response->getRawResponse()['PROFILESTATUS'] );
		$this->assertEquals( "Success", $response->getRawResponse()['ACK'] );
	}

	public function testApprovePayment() {
		// set up expectations
		$testParams = [
				'payment_token' => 'EC-TESTTOKEN12345678910',
				'processor_contact_id' => 'FLJLQ2GV38E4Y',
				'order_id' => '15190.1',
				'amount' => '20.00',
				'currency' => 'USD',
				'description' => 'test DoExpressCheckouPayment',
		];

		$testApiResponse = $this->getTestData( 'DoExpressCheckoutPayment.response' );
		parse_str( $testApiResponse, $parsedTestApiResponse );

		$this->api->expects( $this->once() )
			->method( 'doExpressCheckoutPayment' )
			->with( $this->equalTo( $testParams ) )
			->willReturn( $parsedTestApiResponse );

		// call the code
		$response = $this->provider->approvePayment( $testParams );

		// check the results
		$this->assertInstanceOf( 'SmashPig\PaymentProviders\Responses\ApprovePaymentResponse', $response );
		$this->assertTrue( $response->isSuccessful() );
		$this->assertEquals( "complete", $response->getStatus() );
		$this->assertEquals( "Success", $response->getRawStatus() );
		$this->assertEquals( "Completed", $response->getRawResponse()['PAYMENTINFO_0_PAYMENTSTATUS'] );
		$this->assertEquals( "Success", $response->getRawResponse()['ACK'] );
	}

	private function getTestData( $testFileName ) {
		$testFileDir = __DIR__ . '/../Data/';
		$testFilePath = $testFileDir . $testFileName;
		if ( file_exists( $testFilePath ) ) {
			return file_get_contents( $testFilePath );
		}
	}

}
