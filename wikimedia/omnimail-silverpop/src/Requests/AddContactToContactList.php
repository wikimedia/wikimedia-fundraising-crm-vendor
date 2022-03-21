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

class AddContactToContactList extends SilverpopBaseRequest
{

    /**
     * Identifier for the contact to retrieve.
     *
     * @var int
     */
    protected $contactIdentifier;

    /**
     * @return int
     */
    public function getContactIdentifier(): ?int {
        return $this->contactIdentifier;
    }

    /**
     * @param int $contactIdentifier
     *
     * @return AddContactToContactList
     */
    public function setContactIdentifier(int $contactIdentifier): AddContactToContactList {
        $this->contactIdentifier = $contactIdentifier;
        return $this;
    }

    /**
     * Identifier for the group to retrieve.
     *
     * @var string
     */
    protected $groupIdentifier;

    /**
     * @return string
     */
    public function getGroupIdentifier(): int {
        return $this->groupIdentifier;
    }

    /**
     * @param string $groupIdentifier
     *
     * @return AddContactToContactList
     */
    public function setGroupIdentifier(string $groupIdentifier): AddContactToContactList {
        $this->groupIdentifier = $groupIdentifier;
        return $this;
    }

    /**
     * Get Response
     *
     * @return Contact
     */
    public function getResponse() {
        $result = $this->silverPop->addContactToContactList([
            'contactListID' => $this->getGroupIdentifier(),
            'contactID' => $this->getContactIdentifier(),
        ]);
        if ($result) {
            $response = new Contact([]);
            $response->setContactReference($this->getContactIdentifier());
            $response->setGroupIdentifier($this->getGroupIdentifier());
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
        return [];
    }

}
