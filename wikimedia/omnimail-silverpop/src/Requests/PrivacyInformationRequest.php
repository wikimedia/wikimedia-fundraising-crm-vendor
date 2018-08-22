<?php

namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\GroupMembersResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use Omnimail\Silverpop\Responses\Contact;

class PrivacyInformationRequest extends SilverpopBaseRequest
{

  /**
   * Array of emails to request information for to honour an information request.
   *
   * @var array
   */
  protected $email;

    /**
     * Silverpop database ID.
     *
     * @var int
     */
  protected $database_id;

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
  public function getEmail() {
     return $this->email;
   }

  /**
   * @param array $email
   */
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
   * Get Response
   *
   * @return GroupMembersResponse
   */
  public function getResponse() {
    $result = $this->requestData();
    // @todo if we want to use this then do a lot more here. This has really only
    // been developed as an easier-to-test variant of erase so am reluctant to
    // make decisions now as to how to display data.
    $optInTimestamp = $result['data']['contacts'][0]['data']['consents'][0]['consentDate'] . ' GMT';
    $response = new Contact([
      'opt_in_timestamp' => strtotime($optInTimestamp),
    ]);
    return $response;
  }

  /**
   * Request data from the provider.
   */
  protected function requestData() {
    return $this->silverPop->gdpr_access(['data' => [['Email', $this->getEmail()]], 'database_id' => $this->getDatabaseId()]);
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return array(
      'endpoint' => 'https://api4.ibmmarketingcloud.com',
    );
  }

}
