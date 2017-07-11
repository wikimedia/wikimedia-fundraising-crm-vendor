<?php

namespace Omnimail\Silverpop\Tests;

use Omnimail\Omnimail;
use Omnimail\Silverpop\Credentials;
use Omnimail\Silverpop\Responses\RecipientsResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use Omnimail\Silverpop\Tests\BaseTestClass;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class SilverpopTest extends BaseTestClass {

    /**
     * Container to collect outgoing requests.
     *
     * @var array
     */
    protected $container = array();

    /**
     * Test retrieving mailings.
     */
    public function testAuthenticate() {
        $requests = array(file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'));
        Omnimail::create('Silverpop', array('client' => $this->getMockRequest($requests, false), 'credentials' => new Credentials(array('username' => 'Shrek', 'password' => 'Fiona'))))->getMailings();
        $this->assertOutgoingRequest('Authenticate.txt');
    }

    /**
     * Test retrieving mailings.
     */
    public function testGetMailings() {
        $requests = array(file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'));
        /* @var $request \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest */
        $request = Omnimail::create('Silverpop', array('client' => $this->getMockRequest($requests)))->getMailings();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\MailingsResponse'));
    }

  /**
   * Test retrieving mailings.
   */
  public function testGetRecipients() {
    $requests = array(
      file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'),
      file_get_contents(__DIR__ . '/Responses/AggregateGetResponse1.txt'),
    );
    /* @var $request \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest */
    $request = Omnimail::create('Silverpop', array('client' => $this->getMockRequest($requests)))->getRecipients();
    $response = $request->getResponse();
    $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\RecipientsResponse'));
  }

  /**
   * Test retrieving a mailing group.
   */
  public function testGetGroupMembers() {
    $requests = array(
      file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'),
      file_get_contents(__DIR__ . '/Responses/ExportListResponse.txt'),
    );
    /* @var $request \Omnimail\Silverpop\Requests\ExportListRequest */
    $request = Omnimail::create('Silverpop', array('client' => $this->getMockRequest($requests)))->getGroupMembers();
    $response = $request->getResponse();
    $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\GroupMembersResponse'));
  }

  /**
   * Get mock guzzle client object.
   * @param array $body
   * @param bool $authenticateFirst
   * @return \GuzzleHttp\Client
   */
    public function getMockRequest($body = array(), $authenticateFirst = TRUE) {
      $history = Middleware::history($this->container);
      $responses = array();
      if ($authenticateFirst) {
        $responses[] = new Response(200, [], file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'));
      }
      foreach ($body as $responseBody) {
        $responses[] = new Response(200, [], $responseBody);
      }
      $mock = new MockHandler($responses);
      $handler = HandlerStack::create($mock);
      // Add the history middleware to the handler stack.
      $handler->push($history);
      return new Client(array('handler' => $handler));
    }

    protected function assertOutgoingRequest($fileName)
    {
        $xml = strval($this->container[0]['request']->getBody());
        $this->assertEquals(file_get_contents(__DIR__ . '/Requests/' . $fileName), $xml);
    }

}
