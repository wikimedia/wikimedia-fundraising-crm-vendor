<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Responses;

use SilverpopConnector\SilverpopConnector;
use SilverpopConnector\SilverpopConnectorException;
use Omnimail\Exception\InvalidRequestException;

class Contact {

  /**
   * @var array
   */
  protected $data = [];

  /**
   * @var string
   */
  protected $email;

  /**
   *  Provider identifier for the contact.
   *
   * @var string
   */
  protected $contactIdentifier;

  /**
   * @var string The field that maps to the contact reference.
   *
   *   In silverpop this is a totally custom field so it could be 'anything'.
   *   We use ContactID as a default, but if you use something different you
   *   need to set the field name.
   */
  protected $contactReferenceField;

  /**
   * @return string
   */
  public function getContactReferenceField() {
    return empty($this->contactReferenceField) ? 'ContactID' : $this->contactReferenceField;
  }

  /**
   * @param string $contactReferenceField
   */
  public function setContactReferenceField($contactReferenceField) {
    $this->contactReferenceField = $contactReferenceField;
  }

  /**
   * @var bool
   */
  protected $optOut;

  /**
   * @var string timestamp
   */
  protected $optOutTimestamp;

  /**
   * @var string unix timestamp for when opted in.
   */
  protected $optInTimestamp;

  /**
   * @var
   */
  protected $optInSource;

  /**
   * @var string Data about opt in source.
   */
  protected $optOutSource;

  /**
   * @return bool
   */
  public function isOptOut() {
    return $this->data['Opted Out'] === 'T';
  }

  /**
   * @return mixed
   */
  public function getOptInTimestamp() {
    return isset($this->data['opt_in_timestamp']) ? (string) $this->data['opt_in_timestamp'] : strtotime($this->data['Opt In Date']);
  }

  /**
   * @return false|string
   */
  public function getOptOutIsoDateTime() {
    return (empty($this->getOptOutTimestamp()) ? FALSE : date('Y-m-d H:i:s', $this->getOptOutTimestamp()));
  }

  /**
   * @return mixed
   */
  public function getOptInSource() {
    return (string) $this->data['Opt In Details'];
  }

  /**
   * @return mixed
   */
  public function getOptOutSource() {
    return $this->data['Opt Out Details'];
  }

  /**
   * @var string
   */
  protected $contactReference;

  /**
   * @return string
   */
  public function getEmail() {
    return (string) addslashes($this->email ?: $this->data['Email']);
  }

  /**
   * @param string $email
   */
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
   * @return mixed
   */
  public function getOptOutTimestamp() {
    return $this->data['Opted Out Date'] ? strtotime($this->data['Opted Out Date']) : FALSE;
  }

  public function getOptInIsoDateTime() {
    return date('Y-m-d H:i:s', $this->getOptInTimestamp());
  }

  /**
   * @return mixed
   */
  public function getContactReference() {
    return (string) $this->contactReference ?: $this->data['ContactID'];
  }

  /**
   * @param mixed $contactReference
   */
  public function setContactReference($contactReference) {
    $this->contactReference = $contactReference;
  }

  public function getCustomData($key) {
    return (string) $this->data[$key];
  }

  /**
   * @var  SilverpopConnector
   */
  protected $silverPop;

  public function __construct($data) {
    $this->data = $data;
  }

  /**
   * Get Silverpop connector object.
   *
   * @return \SilverpopConnector\SilverpopConnector
   */
  protected function getSilverPop() {
    if (!$this->silverPop) {
      $this->silverPop = SilverpopConnector::getInstance();
    }
    return $this->silverPop;
  }

}
