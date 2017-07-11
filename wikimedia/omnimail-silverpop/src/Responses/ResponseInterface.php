<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 10:35 AM
 */

namespace Omnimail\Silverpop\Responses;

/**
 * Interface RequestInterface
 *
 * @package Omnimail\Response
 */
interface ResponseInterface {

    /**
     * Is the data available yet.
     */
    public function isCompleted();
}
