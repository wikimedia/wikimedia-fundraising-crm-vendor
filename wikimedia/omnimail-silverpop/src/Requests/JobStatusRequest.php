<?php

namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\EraseResponse;
use Omnimail\Silverpop\Responses\GroupMembersResponse;
use Omnimail\Silverpop\Responses\JobStatusResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use Omnimail\Silverpop\Responses\Contact;

class JobStatusRequest extends SilverpopBaseRequest
{

    /**
     * ID of the job.
     *
     * @var int
     */
    protected int $jobID;

    public function setJobID(int $jobID) {
      $this->jobID = $jobID;
    }

    /**
     * Get Response
     *
     * @return EraseResponse
     */
    public function getResponse() {
        $result = $this->silverPop->getJobStatus($this->jobID);
        $response = new JobStatusResponse(['status' => $result]);
        return $response;
    }

}
