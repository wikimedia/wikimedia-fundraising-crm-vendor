<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Exception\Exception;
use Omnimail\Silverpop\Responses\Contact;

/**
 * @implements \Omnimail\Silverpop\Requests\RequestInterface
 */
class AddRecipient extends SilverpopBaseRequest
{

  /**
   * Identifier for the groups to add the contact to.
   *
   * @var int[]
   */
  protected $groupIdentifier;

  /**
   * The data base to be updated.
   *
   * @var int
   */
  protected $databaseID;

  /**
   * Email to add.
   *
   * @var string
   */
  protected $email;

  /**
   * Email to add.
   *
   * @var string
   */
  protected $snoozeTimeStamp;

  /**
   * @var array
   */
  protected $fields;

    /**
     * Get the snooze end date - in the insane date format the api likes...
     *
     * @return string|null
     */
  protected function getSnoozeDate(): ?string {
    return $this->snoozeTimeStamp ? date('m/d/Y', $this->snoozeTimeStamp) : NULL;
  }

  protected function getSnoozeTimeStamp() {
    return $this->snoozeTimeStamp;
  }

  /**
   * @param string|null $snoozeTimeStamp
   *
   * @return AddRecipient
   */
  public function setSnoozeTimeStamp(?string $snoozeTimeStamp): AddRecipient {
    $this->snoozeTimeStamp = $snoozeTimeStamp;
    return $this;
  }

    /**
   * @return array
   */
  public function getFields(): array {
    return array_merge(['Email' => $this->getEmail()], $this->fields);
  }

  /**
   * @param array $fields
   *
   * @return self
   */
  public function setFields(array $fields): self {
    $this->fields = $fields;
    return $this;
  }

  /**
   * @return string
   */
  public function getEmail(): string {
    return $this->email;
  }

  /**
   * @param string $email
   *
   * @return self
   */
  public function setEmail(string $email): self {
    $this->email = $email;
    return $this;
  }

  /**
   * @return int
   */
  public function getDatabaseID(): int {
    return $this->databaseID;
  }

  /**
   * @param int $databaseID
   *
   * @return self
   */
  public function setDatabaseID(int $databaseID): self {
    $this->databaseID = $databaseID;
    return $this;
  }

  /**
   * @return array
   */
  public function getGroupIdentifier() {
    return $this->groupIdentifier;
  }

  /**
   * @param int[] $groupIdentifier
   */
  public function setGroupIdentifier($groupIdentifier): void {
    $this->groupIdentifier = $groupIdentifier;
  }

  /**
   * Get Response
   *
   * @return Contact
   */
  public function getResponse() {
    $result = $this->silverPop->addRecipient(
      $this->getDatabaseID(),
      $this->getFields(),
      TRUE,
      FALSE,
      3,
      $this->getGroupIdentifier(),
    );
    if ($this->getSnoozeDate()) {
      $updateResult = $this->silverPop->updateRecipient(
        $this->getDatabaseID(),
          (int) $result[0],
        [],
        ['snoozeDate' => $this->getSnoozeDate()],
      );
    }
    if ($result) {
      $response = new Contact([]);
      $response->setGroupIdentifier($this->getGroupIdentifier());
      $response->setContactIdentifier((int) $result[0]);
      return $response;
    }
    throw new Exception('Update failed.');
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return parent::getDefaultParameters();
  }

}
