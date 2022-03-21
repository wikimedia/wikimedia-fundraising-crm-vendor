<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\GroupResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use phpDocumentor\Reflection\Types\Static_;
use SilverpopConnector\Xml\CreateContactList;

class CreateContactListRequest extends SilverpopBaseRequest
{

    /**
     * The name for the created contact list.
     *
     * @required
     *
     * @var string
     */
    protected $name;

    /**
     * Defines the visibility of the created contact list: 0 (private) or 1 (shared).
     *
     * @var int
     */
    protected $visibility = 1;

    /**
     * The associated database ID for the contact list.
     *
     * @var int
     */
    protected $databaseID;

    /**
     * Parent folder path.
     *
     * Specifies the contact list folder path of the contact list folder where you want the contact list. The specified folder must exist in the contact list structure and you must have access to the folder.
     *
     * @var string
     */
    protected $parentFolderPath;

    /**
     * PARENT_FOLDER_ID.
     *
     * Specifies the contact list folder ID where you want the contact list.
     * The specified folder must exist in the contact list structure and you must
     * have access to the folder.
     *
     * @var string
     */
    protected $parentFolderID;

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $contactListName
     *
     * @return self
     */
    public function setName(string $contactListName): CreateContactListRequest {
        $this->name = $contactListName;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibility(): int {
        return $this->visibility;
    }

    /**
     * @param int $visibility
     *
     * @return self
     */
    public function setVisibility(int $visibility): self {
        $this->visibility = $visibility;
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
     * @return null|string
     */
    public function getParentFolderPath(): ?string {
        return $this->parentFolderPath;
    }

    /**
     * @param string $parentFolderPath
     *
     * @return self
     */
    public function setParentFolderPath(string $parentFolderPath): self {
        $this->parentFolderPath = $parentFolderPath;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParentFolderID(): ?string {
        return $this->parentFolderID;
    }

    /**
     * @param string $parentFolderID
     *
     * @return self
     */
    public function setParentFolderID(string $parentFolderID): self {
        $this->parentFolderID = $parentFolderID;
        return $this;
    }

    /**
     * @param int $createParentFolder
     *
     * @return self
     */
    public function setCreateParentFolder(int $createParentFolder): self {
        $this->createParentFolder = $createParentFolder;
        return $this;
    }

    /**
     * Should the parent folder be created.
     *
     * If the specified PARENT_FOLDER_PATH doesn’t exist, then the system creates that folder.
     * However, if you have a folder limit assigned at the org level, then the contact list is created from your root folder/.
     *
     * @var bool
     */
    protected $createParentFolder;

    /**
     * @return bool
     */
    public function isCreateParentFolder(): bool {
        return $this->createParentFolder;
    }

  /**
   * Get Response
   *
   * @return GroupResponse
   */
  public function getResponse() {
    $result = $this->silverPop->createContactList([
      'visibility' => $this->getVisibility(),
      'databaseID' => $this->getDatabaseID(),
      'contactListName' => $this->getName(),
      'parentFolderPath' => $this->getParentFolderPath(),
      'parentFolderID' => $this->getParentFolderID(),
    ]);
    $response = new GroupResponse();
    $response->setName($this->getName());
    $response->setListID((int) $result->CONTACT_LIST_ID);
    $response->setParentListID((int) $result->PARENT_FOLDER_ID);
    $response->setSilverpop($this->silverPop);
    return $response;
  }

}
