<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class EraseResponse extends BaseResponse
{
    /**
     * @return array
     */
    public function getRetrievalParameters() {
        return ['fetch_url' => $this['data']['fetch_url'], 'database_id' => $this['data']['database_id']];
    }

    /**
     * Is the erasure completed.
     *
     * @return bool
     */
    public function isCompleted() {
        if (isset($this['data']['status'])) {
           return ($this['data']['status'] === 'COMPLETE');
        }
        return TRUE;
    }

}
