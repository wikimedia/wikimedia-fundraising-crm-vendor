<?php

namespace Omnimail\Silverpop;

use Omnimail\Silverpop\AbstractMailer;
use Omnimail\Silverpop\Requests\GetSentMailingsForOrgRequest;
use Omnimail\Silverpop\Requests\RawRecipientDataExportRequest;
use Omnimail\MailerInterface;
use Omnimail\Silverpop\Requests\RequestInterface;
use Omnimail\Silverpop\Responses\ResponseInterface;
use Omnimail\Silverpop\Responses\Offline\OfflineMailingsResponse;
use Omnimail\Silverpop\Responses\Contact;

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 4/4/17
 * Time: 12:12 PM
 */
class Mailer extends AbstractMailer implements MailerInterface
{

  /**
   * Guzzle client, overridable with mock object in tests.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * @return \GuzzleHttp\Client
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * @param \GuzzleHttp\Client $client
   */
  public function setClient($client) {
    $this->client = $client;
  }

  public function send(\Omnimail\EmailInterface $email) {}

    /**
     * Get an array of the credential fields that are required.
     *
     * @return array
     */
    public function getCredentialFields() {
      return array(
        'username' => array('type' => 'String', 'required' => TRUE),
        'password' => array('type' => 'String', 'required' => TRUE),
        'clientId' => array('type' => 'String', 'required' => FALSE),
        'clientSecret' => array('type' => 'String', 'required' => FALSE),
        'refreshToken' => array('type' => 'String', 'required' => FALSE),
        'engage_server' => array('type' => 'String', 'required' => FALSE, 'default' => 4),
      );
    }

  /**
   * Get Mailings.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\GetSentMailingsForOrgRequest
   */
    public function getMailings($parameters = array()) {
      return $this->createRequest('GetSentMailingsForOrgRequest', array_merge($parameters, array(
        'credentials' => $this->getCredentials(),
        'client' => $this->getClient(),
      )));
    }

  /**
   * Get Group Members.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\ExportListRequest
   */
  public function getGroupMembers($parameters = array()) {
    return $this->createRequest('ExportListRequest', array_merge($parameters, array(
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    )));
  }

  /**
   * Get Group Members.
   *
   * @param array $parameters
   *
   * @see https://developer.goacoustic.com/acoustic-campaign/reference/addcontacttocontactlist
   *
   * @return \Omnimail\Silverpop\Requests\AddContactToContactList
   */
  public function addGroupMembers($parameters = []) {
    return $this->createRequest('AddContactToContactList', array_merge($parameters, array(
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    )));
  }

  /**
   * Get Recipients.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\RemoveRecipientRequest
   */
  public function removeGroupMember($parameters = []) {
    return $this->createRequest('RemoveRecipient', array_merge($parameters, [
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    ]));
  }

  /**
   * Get Contact.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\AddRecipientRequest
   */
  public function addContact($parameters = []) {
    return $this->createRequest('AddRecipient', array_merge($parameters, [
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    ]));
  }

  /**
   * Get Group Members.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\SelectRecipientData
   */
  public function getContact($parameters = []) {
    return $this->createRequest('SelectRecipientData', array_merge($parameters, [
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    ]));
  }

  /**
   * Get Group Members.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\CreateContactListRequest
   */
  public function createGroup($parameters = []) {
    return $this->createRequest('CreateContactListRequest', array_merge($parameters, [
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    ]));
  }

    /**
     * Set up a csv import.
     *
     * This provides information to Acoustic about the csv and mappings that has
     * been uploaded.
     *
     * https://developer.goacoustic.com/acoustic-campaign/reference/importlist
     *
     * @param array $parameters
     *
     * @return \Omnimail\Silverpop\Requests\ImportListRequest
     */
    public function importList($parameters = []) {
        return $this->createRequest('ImportListRequest', array_merge($parameters, [
            'credentials' => $this->getCredentials(),
            'client' => $this->getClient(),
        ]));
    }

  /**
   * Get Recipients.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest
   */
  public function getRecipients($parameters = array()) {
    return $this->createRequest('RawRecipientDataExportRequest', array_merge($parameters, array(
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    )));
  }

  /**
   * Get Recipients.
   *
   * @param array $parameters
   *
   * @return \Omnimail\Silverpop\Requests\WebTrackingDataExportRequest
   */
  public function getWebActions($parameters = []) {
    return $this->createRequest('WebTrackingDataExportRequest', array_merge($parameters, [
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
    ]));
  }

  public function getJobStatus($parameters = []) {
    return $this->createRequest('JobStatusRequest', array_merge($parameters, array(
        'credentials' => $this->getCredentials(),
        'client' => $this->getClient(),
    )));
  }

    /**
     * Get Recipients.
     *
     * @param array $parameters
     *
     * @return \Omnimail\Silverpop\Requests\GetQueryRequest
     */
    public function getQueryCriteria($parameters = []) {
      return $this->createRequest('GetQueryRequest', array_merge($parameters, array(
        'credentials' => $this->getCredentials(),
        'client' => $this->getClient(),
      )));
    }

    /**
     * Get information about whether consent has been given to use a mobile phone for SMS.
     *
     * @param array $parameters
     *   - database_id (int)
     *   - data (array) e.g [['Email', a@example.com']['Email', b@example.com']]
     *
     * @return ConsentInformationRequest
     */
    public function consentInformationRequest($parameters = [])
    {
        return $this->createRequest('ConsentInformationRequest', array_merge($parameters, array(
            'credentials' => $this->getCredentials(),
            'client' => $this->getClient(),
            'is_use_rest' => TRUE,
        )));
    }

    /**
   * Initialize a request object
   *
   * This function is usually used to initialise objects of type
   * BaseRequest (or a non-abstract subclass of it)
   * using existing parameters from this gateway.
   *
   * If a client has been set on this class it will passed through,
   * allowing a mock guzzle client to be used for testing.
   *
   * Example:
   *
   * <code>
   *   function myRequest($parameters) {
   *     $this->createRequest('MyRequest', $parameters);
   *   }
   *   class MyRequest extends SilverpopBaseRequest {};
   *
   *   // Create the mailer
   *   $mailer = Omnimail::create('Silverpop', $parameters);
   *
   *   // Create the request object
   *   $myRequest = $mailer->myRequest($someParameters);
   * </code>
   *
   * @param string $class The request class name
   * @param array $parameters
   *
   * @return \Omnimail\Common\Requests\RequestInterface
   */
  protected function createRequest($class, array $parameters)
  {
    $class = "Omnimail\\Silverpop\\Requests\\" . $class;
    return parent::createRequest($class, $parameters);
  }

  /**
   * Get an object to manage contacts with the provider.
   *
   * @param array $parameters
   *   - database_id (int)
   *   - data (array) e.g [['Email', a@example.com']['Email', b@example.com']]
   *
   * @return Contact
   */
  public function privacyInformationRequest($parameters = [])
  {
    return $this->createRequest('PrivacyInformationRequest', array_merge($parameters, array(
      'credentials' => $this->getCredentials(),
      'client' => $this->getClient(),
      'is_use_rest' => TRUE,
    )));
  }

    /**
     * Get an object to manage contacts with the provider.
     *
     * @param array $parameters
     *   - database_id (int)
     *   - data (array) e.g [['Email', a@example.com']['Email', b@example.com']]
     *
     * @return Contact
     */
    public function privacyDeleteRequest($parameters = []) {
      return $this->createRequest('PrivacyDeleteRequest', array_merge($parameters, array(
        'credentials' => $this->getCredentials(),
         'client' => $this->getClient(),
          'is_use_rest' => TRUE,
       )));
    }

}
