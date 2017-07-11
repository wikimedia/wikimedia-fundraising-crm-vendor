<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 10:35 AM
 */

namespace Omnimail\Silverpop\Requests;

/**
 * Interface RequestInterface
 *
 * @package Omnimail\Requests
 */
interface RequestInterface {

  /**
   * Get a response object, based on the properties that have been set.
   *
   * @return \Omnimail\Responses\BaseResponse
   */
  public function getResponse();

  /**
   * Get the defaults that should be used if properties are not otherwise defined.
   *
   * @return array
   */
  public function getDefaultParameters();
}
