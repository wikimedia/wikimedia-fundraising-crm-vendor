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
class SelectRecipientData extends SilverpopBaseRequest
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
    $result = $this->silverPop->selectRecipientData(
      $this->getDatabaseID(),
      ['Email' => $this->getEmail(), 'RETURN_CONTACT_LISTS' => TRUE]
    );
    if ($result) {
      $response = new Contact([]);
      $groups = [];
      foreach ($result->CONTACT_LISTS as $lists) {
        $groups[] = (int) $lists->CONTACT_LIST_ID;
      }
      $response->setGroupIdentifiers($groups);
      $response->setContactIdentifier($result->RecipientId);
      $response->setEmail((string) $result->Email);
      $response->setOptInTimestamp(empty($result->OptedIn) ? NULL : strtotime($result->OptedIn));
      $response->setOptOutTimestamp(empty($result->OptedOpt) ? NULL : strtotime($result->OptedOut));
      $response->setLastModifiedTimestamp(strtotime($result->LastModified));
      $response->setSnoozeEndTimestamp(empty($result->ResumeSendDate) ? NULL : strtotime($result->ResumeSendDate));
      $fields = [];
      foreach ($result->COLUMNS->COLUMN as $column) {
        $fields[(string) $column->NAME] = (string) $column->VALUE;
      }
      $response->setFields($fields);
      return $response;
    }
    throw new Exception('Update failed.');
  }

  /**
   * Request data from the provider.
   */
  protected function requestData() {
    $result = $this->silverPop->exportList(
      $this->getGroupIdentifier(),
      $this->getStartTimeStamp(),
      $this->getEndTimeStamp(),
      $this->getExportType()
    );
    $this->setRetrievalParameters(array(
      'jobId' => (string) $result['jobId'],
      'filePath' => (string) $result['filePath'],
    ));
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
