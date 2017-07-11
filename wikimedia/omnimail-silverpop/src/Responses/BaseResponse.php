<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

use Omnimail\Common\Helper;

abstract class BaseResponse extends \arrayObject implements ResponseInterface
{
  /**
   * @var \SilverpopConnector\SilverpopXmlConnector
   */
  protected $silverPop;

  /**
   * Timestamp for start of period.
   *
   * @var int
   */
  protected $startTimeStamp;

  /**
   * Timestamp for end of period.
   *
   * @var int
   */
  protected $endTimeStamp;


  /**
   * Url to direct requests to.
   *
   * @var string
   */
  protected $endPoint;

  /**
   * @return string
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * @param string $endPoint
   */
  public function setEndPoint($endPoint) {
    $this->endPoint = $endPoint;
  }

  /**
   * @return int
   */
  public function getStartTimeStamp() {
    return $this->startTimeStamp;
  }

  /**
   * @param int $startTimeStamp
   */
  public function setStartTimeStamp($startTimeStamp) {
    $this->startTimeStamp = $startTimeStamp;
  }

  /**
   * @return int
   */
  public function getEndTimeStamp() {
    return $this->endTimeStamp;
  }

  /**
   * @param int $endTimeStamp
   */
  public function setEndTimeStamp($endTimeStamp) {
    $this->endTimeStamp = $endTimeStamp;
  }

  /**
   * @param \SilverpopConnector\SilverpopXmlConnector $silverpop
   */
  public function setSilverpop($silverpop) {
    $this->silverPop = $silverpop;
  }

  /**
   * @retutn \SilverpopConnector\SilverpopXmlConnector
   */
  public function getSilverpop() {
    return $this->silverPop;
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return array(
      'endpoint' => 'https://api4.silverpop.com',
    );
  }

  /**
   * Is the data available yet.
   */
  public function isCompleted() {
    TRUE;
  }

}
