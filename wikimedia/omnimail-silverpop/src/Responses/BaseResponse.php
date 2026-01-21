<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

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
   * Offset from start of csv file.
   *
   * @var int
   */
  protected $offset = 0;

  /**
   * @return int
   */
  public function getOffset() {
    return $this->offset;
  }

  /**
   * @param int $offset
   */
  public function setOffset($offset) {
    $this->offset = $offset;
  }

  /**
   * Rows to retrieve.
   *
   * @var int
   */
  protected $limit = 0;

  /**
   * @return int
   */
  public function getLimit(): int {
    return $this->limit;
  }

  /**
   * @param int $limit
   */
  public function setLimit(int $limit) {
    $this->limit = $limit;
  }

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
  public function getDefaultParameters(): array {
    return [
      'endpoint' => 'https://api-campaign-us-4.goacoustic.com',
    ];
  }

  /**
   * Is the data available yet.
   */
  public function isCompleted() {
    TRUE;
  }

}
