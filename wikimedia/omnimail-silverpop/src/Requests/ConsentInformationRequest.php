<?php

namespace Omnimail\Silverpop\Requests;

use GuzzleHttp\Exception\ClientException;
use Omnimail\Silverpop\Responses\GroupMembersResponse;
use Omnimail\Silverpop\Responses\Consent;

/**
 * https://api-campaign-us-2.goacoustic.com/restdoc/#!/databases/consent_get_get_10
 */
class ConsentInformationRequest extends SilverpopBaseRequest
{

    /**
     * Silverpop database ID.
     *
     * @var int
     */
  protected $database_id;

  protected string $channel = 'SMS';

  protected string $shortCode;

    /**
     * Qualifier, aka text to join.
     *
     * This is the SMS number that a person would send text to to consent.
     *
     * @var string
     *
     */
  protected $qualifier;

    /**
     * Destination - this is generally the phone number with country code but no leading zeros.
     * @var int
     */
  protected $destination;

  private string $phone;

  /**
   * @return int
   */
  public function getDatabaseId() {
    return $this->database_id;
  }

  /**
   * @param int $database_id
   */
  public function setDatabaseId($database_id) {
    $this->database_id = $database_id;
  }

  /**
   * @return string
   */
  public function getPhone(): string {
     return $this->phone;
   }

  /**
   * @param string $phone
   */
  public function setPhone(string $phone) {
    $this->phone = $phone;
  }

  /**
   * Get Response
   *
   * @return GroupMembersResponse
   */
  public function getResponse() {
    $result = $this->requestData();
    $response = new Consent($result['data']);
    return $response;
  }

  /**
   * Request data from the provider.
   */
  protected function requestData() {
      try {
          return $this->silverPop->restGet(
              $this->getDatabaseId(),
              'databases',
              ['consent', $this->channel . '-' . $this->getShortCode(), $this->getPhone()]);
      }
      catch (ClientException $e) {
          // 404 here indicates no consent record found.
          if ($e->getResponse()->getStatusCode() == 404) {
              return ['data' => []];
          }
          throw $e;
      }
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters(): array {
    return [
      'endpoint' => 'https://api-campaign-us-4.goacoustic.com',
    ];
  }

    public function getShortCode(): string {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): self {
        $this->shortCode = $shortCode;
        return $this;
    }

}
