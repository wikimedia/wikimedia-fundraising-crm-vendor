<?php
namespace Omnimail\Silverpop\Responses;

use phpseclib\Net\Sftp;
use League\Csv\Reader;
use League\Csv\Statement;
use Omnimail\Silverpop\Responses\Contact;

class ImportListResponse extends BaseResponse {

    protected $isSuccess;

    protected $jobId;

    /**
     * @return mixed
     */
    public function getJobId() {
        return $this->jobId;
    }

    /**
     * @param mixed $jobId
     *
     * @return ImportListResponse
     */
    public function setJobId($jobId) {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSuccess() {
        return $this->isSuccess;
    }

    /**
     * @param mixed $isSuccess
     *
     * @return ImportListResponse
     */
    public function setIsSuccess($isSuccess) {
        $this->isSuccess = $isSuccess;
        return $this;
    }

}
