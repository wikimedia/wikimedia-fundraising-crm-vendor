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
  protected $data = array();

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
    return $this->data['opted_out'] == 'T' ? TRUE : FALSE;
  }

  /**
   * @return mixed
   */
  public function getOptInTimestamp() {
    return $this->data['opt_in_timestamp'];
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
    return (string) $this->data['opt_in_details'];
  }

  /**
   * @return mixed
   */
  public function getOptOutSource() {
    return $this->data['opt_out_details'];
  }

  /**
   * @var string
   */
  protected $contactReference;

  /**
   * @return string
   */
  public function getEmail() {
    return (string) $this->email ?: $this->data['email'];
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
    return $this->data['opted_out_timestamp'];
  }

  public function getOptInIsoDateTime() {
    return (date('Y-m-d H:i:s', $this->getOptInTimestamp()));
  }

  /**
   * @return mixed
   */
  public function getContactReference() {
    return (string) $this->contactReference ?: $this->data['contactid'];
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
