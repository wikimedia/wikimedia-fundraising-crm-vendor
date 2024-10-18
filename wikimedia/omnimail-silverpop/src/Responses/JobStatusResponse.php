<?php

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Responses;

class JobStatusResponse extends BaseResponse {

    private array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * Is the erasure completed.
     *
     * @return bool
     */
    public function isCompleted() {
        if (isset($this->data['status'])) {
            return ($this->data['status'] === 'COMPLETE');
        }
        return TRUE;
    }

}
