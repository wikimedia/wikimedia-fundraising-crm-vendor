<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\GroupResponse;
use Omnimail\Silverpop\Responses\JobStatusResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use phpDocumentor\Reflection\Types\Static_;

class RemoveRecipient extends SilverpopBaseRequest
{

    /**
     * Email
     *
     * Email to be removed.
     *
     * @var string
     */
    protected $email;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): RemoveRecipient
    {
        $this->email = $email;
        return $this;
    }

    /**
     * List ID.
     *
     * ID of the group/list/query.
     *
     * @var string
     */
    protected $listId;

    public function getListId(): string
    {
        return $this->listId;
    }

    public function setListId(string $listId): RemoveRecipient
    {
        $this->listId = $listId;
        return $this;
    }

    /**
     * Get Response
     *
     * @return GroupResponse
     */
    public function getResponse()
    {
        $result = $this->silverPop->removeRecipient(
            $this->getListId(),
            $this->getEmail()
        );
        // Assume success as otherwise an exception is thrown.
        $response = new JobStatusResponse(['status' => 'COMPLETE']);
        return $response;
    }

}
