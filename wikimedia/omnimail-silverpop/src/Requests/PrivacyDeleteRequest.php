<?php

namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\GroupMembersResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use Omnimail\Silverpop\Responses\Contact;

class PrivacyDeleteRequest extends SilverpopBaseRequest
{

    /**
     * Single email or array of emails to delete information for to honour an information deletion request.
     *
     * @var array|string
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
        $response = new Contact($this->requestData());
        return $response;
    }

    /**
     * Request data from the provider.
     */
    protected function requestData() {
        return $this->silverPop->gdpr_erasure(['data' => $this->getEmailArray(), 'database_id' => $this->getDatabaseId()]);
    }

    protected function getEmailArray() {
      if (!is_array($this->getEmail())) {
        return [['Email', $this->getEmail()]];
      }
      else {
        $return = [];
        foreach ($this->getEmail() as $email) {
          $return[] = ['Email' , $email];
        }
      }
      return $return;
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
