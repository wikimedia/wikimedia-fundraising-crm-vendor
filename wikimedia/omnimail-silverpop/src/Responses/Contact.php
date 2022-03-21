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
  * Acoustic Identifier for the group or list.
  *
  * @var int
  */
  protected $groupIdentifier;

  /**
   * Acoustic Identifier for the group or list.
   *
   * @var array
   */
  protected $groupIdentifiers = [];

  /**
   * @return int|null
   */
  public function getGroupIdentifier(): ?int {
      return is_numeric($this->groupIdentifier) ? $this->groupIdentifier : reset($this->groupIdentifiers);
  }

  /**
   * @return array
   */
  public function getGroupIdentifiers(): array {
    return !empty($this->groupIdentifier) ? [$this->groupIdentifier] : $this->groupIdentifiers;
  }

  /**
   * @param array $groupIdentifiers
   */
  public function setGroupIdentifiers(array $groupIdentifiers): void {
    $this->groupIdentifiers = $groupIdentifiers;
  }

  /**
   * @param int|array $groupIdentifier
   *
   * @return Contact
   */
  public function setGroupIdentifier($groupIdentifier): Contact {
      $this->groupIdentifier = (array) $groupIdentifier;
      return $this;
  }

    /**
     * @return string
     */
    public function getContactIdentifier(): string {
        return $this->contactIdentifier;
    }

    /**
     * @param string $contactIdentifier
     *
     * @return Contact
     */
    public function setContactIdentifier(string $contactIdentifier): Contact {
        $this->contactIdentifier = $contactIdentifier;
        return $this;
    }

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
   * @param string $optInTimestamp
   *
   * @return Contact
   */
  public function setOptInTimestamp(string $optInTimestamp): Contact {
    $this->optInTimestamp = $optInTimestamp;
    return $this;
  }

  /**
   * @param string|null $optInTimestamp
   *
   * @return Contact
   */
  public function setOptOutTimestamp(?string $optOutTimestamp): Contact {
    $this->optOutTimestamp = $optOutTimestamp;
    return $this;
  }

  /**
   * Last modified date.
   *
   * @var string
   */
  protected $lastModifiedTimestamp;

  /**
   * End of sending pause.
   *
   * @var string
   */
  protected $snoozeEndTimestamp;

  /**
   * @return string
   */
  public function getLastModified(): string {
    return $this->lastModifiedTimestamp;
  }

  /**
   * @return false|string
   */
  public function getLastModifiedIsoDateTime() {
    return (empty($this->getLastModified()) ? FALSE : date('Y-m-d H:i:s', $this->getLastModified()));
  }

  /**
   * @param string|null $lastModified
   *
   * @return Contact
   */
  public function setLastModifiedTimestamp(?string $lastModified): Contact {
    $this->lastModifiedTimestamp = $lastModified;
    return $this;
  }

  /**
   * Set the timestamp for emails to resume.
   *
   * @param string|null $snoozeEndTimestamp
   *
   * @return Contact
   */
  public function setSnoozeEndTimestamp(?string $snoozeEndTimestamp): Contact {
    $this->snoozeEndTimestamp = $snoozeEndTimestamp;
    return $this;
  }


  /**
   * Set the timestamp for emails to resume.
   *
   * @return string|bool
   */
  public function getSnoozeEndTimestamp() {
    return $this->snoozeEndTimestamp;
  }

  /**
   * Set the timestamp for emails to resume.
   *
   * @return string|bool
   */
  public function getSnoozeEndISODateTime() {
    return (empty($this->getSnoozeEndTimestamp()) ? FALSE : date('Y-m-d H:i:s', $this->getSnoozeEndTimestamp()));
  }

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
   * Ad hoc fields as an array.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * @return array
   */
  public function getFields(): array {
    return $this->fields;
  }

  /**
   * @param array $fields
   *
   * @return Contact
   */
  public function setFields(array $fields): Contact {
    $this->fields = $fields;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOptInTimestamp() {
    if ($this->optInTimestamp) {
      return $this->optInTimestamp;
    }
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
    if (!empty($this->optOutTimestamp)) {
      return $this->optOutTimestamp;
    }
    return !empty($this->data['Opted Out Date']) ? strtotime($this->data['Opted Out Date']) : FALSE;
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
   * Load the contact from Acoustic
   */
  public function load(): void {
    $data = $this->getSilverPop()->selectRecipientData($this->getGroupIdentifier(), [], []);
  }

  /**
   * Get Silverpop connector object.
   *
   * @return \SilverpopConnector\SilverpopXmlConnector
   */
  protected function getSilverPop() {
    if (!$this->silverPop) {
      $this->silverPop = SilverpopConnector::getInstance();
    }
    return $this->silverPop;
  }

}
