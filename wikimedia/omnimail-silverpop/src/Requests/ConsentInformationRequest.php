<?php

namespace Omnimail\Silverpop\Requests;

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

    private int $phone;

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
   * @return array
   */
  public function getPhone() {
     return $this->phone;
   }

  /**
   * @param int $phone
   */
  public function setPhone(int $phone) {
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
    return $this->silverPop->restGet(
        $this->getDatabaseId(),
        'databases',
        ['consent', $this->channel . '-' . $this->getShortCode(), $this->getPhone()]);
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
