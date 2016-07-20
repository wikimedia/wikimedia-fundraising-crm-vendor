<?php
namespace SmashPig\PaymentProviders\Amazon\Test;

use SmashPig\PaymentProviders\Amazon\AmazonApi;
use SmashPig\Tests\BaseSmashPigUnitTestCase;

class ApiTest extends BaseSmashPigUnitTestCase {

	protected $mockClient;

	public function setUp() {
		parent::setUp();
		chdir( __DIR__ . '/..' ); // So the mock client can find its response files
		$config = $this->setConfig( 'amazon',  __DIR__ . '/../config_test.yaml' );
		$this->mockClient = $config->object( 'payments-client', true );
		$this->mockClient->calls = array();
		$this->mockClient->returns = array();
		$this->mockClient->exceptions = array();
	}

	public function testFindParent() {
		$this->mockClient->returns['getAuthorizationDetails'][] = 'Declined';
		$this->mockClient->returns['getAuthorizationDetails'][] = 'Closed';
		$parentId = AmazonApi::findRefundParentId( 'P01-0133129-0199515-R019658' );
		$this->assertEquals( 'P01-0133129-0199515-C019658', $parentId, 'Did not get the right refund parent ID' );
	}
}
