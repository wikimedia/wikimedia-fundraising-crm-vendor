<?php

namespace Omnimail\Silverpop\Requests;

/**
 * Send a virtual mobile originated (MO) message.
 *
 * Acoustic handles this as if the phone number had texted the program, which
 * opts it into that program.
 *
 * https://developer.goacoustic.com/acoustic-campaign/reference/send-a-mobile-originated-mo-message
 */
class SmsOptInRequest extends SilverpopBaseRequest
{

    /**
     * Silverpop database ID.
     *
     * Not used by this request but the base request sets it from the credentials.
     *
     * @var int
     */
  protected $database_id;

    /**
     * Text-for-Response program to opt the phone number into.
     *
     * @var string
     */
  protected string $programID;

    /**
     * Phone number, including country code and without a leading plus, eg. 14155552671.
     *
     * @var string
     */
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
  public function getProgramID(): string {
    return $this->programID;
  }

  /**
   * @param string $programID
   */
  public function setProgramID(string $programID): self {
    $this->programID = $programID;
    return $this;
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
   * @return array
   */
  public function getResponse() {
    return $this->silverPop->restPost(
      $this->getProgramID(),
      'channels/sms/programs',
      ['virtualmo'],
      ['phoneNumber' => $this->getPhone()]
    );
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

}