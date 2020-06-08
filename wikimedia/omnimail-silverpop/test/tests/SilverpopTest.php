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
    protected $container = [];

    /**
     * Test retrieving mailings.
     */
    public function testAuthenticate() {
        $requests = [file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt')];
        Omnimail::create('Silverpop', ['client' => $this->getMockRequest($requests, FALSE), 'credentials' => new Credentials(['username' => 'Shrek', 'password' => 'Fiona'])])->getMailings();
        $this->assertOutgoingRequest('Authenticate.txt');
    }

    /**
     * Test retrieving mailings.
     */
    public function testGetMailings() {
        $requests = [file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt')];
        /* @var $request \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest */
        $request = Omnimail::create('Silverpop', ['client' => $this->getMockRequest($requests)])->getMailings();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\MailingsResponse'));
    }

    /**
     * Test the getQuery function.
     */
    public function testGetQuery() {
        $requests = [
            file_get_contents(__DIR__ . '/Responses/GetQueryResponse.txt'),
        ];
        /* @var $request \Omnimail\Silverpop\Requests\GetQueryRequest */
        $request = Omnimail::create('Silverpop', ['client' => $this->getMockRequest($requests)])->getQueryCriteria();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\QueryCriteriaResponse'));
        $this->assertEquals('Super witty name written by a master of words', $response->getQueryName());
        $this->assertEquals('( is in contact list 1234567 AND Segment is equal to 328 AND latest_donation_date is before 01/01/2019 ) OR Email is equal to info@example.org', $response->getQueryCriteria());
    }

    /**
     * Test retrieving mailings.
     */
    public function testGetRecipients() {
        $requests = [
            file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'),
            file_get_contents(__DIR__ . '/Responses/AggregateGetResponse1.txt'),
        ];
        /* @var $request \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest */
        $request = Omnimail::create('Silverpop', ['client' => $this->getMockRequest($requests)])->getRecipients();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\RecipientsResponse'));
    }

    /**
     * Test retrieving a mailing group.
     */
    public function testGetGroupMembers() {
        $requests = [
            file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'),
            file_get_contents(__DIR__ . '/Responses/ExportListResponse.txt'),
        ];
        /* @var $request \Omnimail\Silverpop\Requests\ExportListRequest */
        $request = Omnimail::create('Silverpop', ['client' => $this->getMockRequest($requests)])->getGroupMembers();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\GroupMembersResponse'));
    }

    /**
     * Get mock guzzle client object.
     *
     * @param array $body
     * @param bool $authenticateFirst
     *
     * @return \GuzzleHttp\Client
     */
    public function getMockRequest($body = [], $authenticateFirst = TRUE) {
        $history = Middleware::history($this->container);
        $responses = [];
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
        return new Client(['handler' => $handler]);
    }

    protected function assertOutgoingRequest($fileName) {
        $xml = strval($this->container[0]['request']->getBody());
        $this->assertEquals(file_get_contents(__DIR__ . '/Requests/' . $fileName), $xml);
    }

}
