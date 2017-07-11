<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\GroupMembersResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;

class ExportListRequest extends SilverpopBaseRequest
{

  protected $retrievalParameters;

  /**
   * Identifier for the group to retrieve.
   *
   * @var string
   */
  protected $groupIdentifier;

  /**
   * @return mixed
   */
  public function getGroupIdentifier() {
    return $this->groupIdentifier;
  }

  /**
   * @param mixed $groupIdentifier
   */
  public function setGroupIdentifier($groupIdentifier) {
    $this->groupIdentifier = $groupIdentifier;
  }

  /**
   * @return array
   */
  public function getRetrievalParameters() {
    return $this->retrievalParameters;
  }

  /**
   * @param array $retrievalParameters
   */
  public function setRetrievalParameters($retrievalParameters) {
    $this->retrievalParameters = $retrievalParameters;
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
   * Get Response
   *
   * @return GroupMembersResponse
   */
  public function getResponse() {

    if (!$this->getRetrievalParameters()) {
      $this->requestData();
    }
    $response = new GroupMembersResponse(array());
    $response->setRetrievalParameters($this->getRetrievalParameters());
    $response->setSilverpop($this->silverPop);
    return $response;
  }

  /**
   * Request data from the provider.
   */
  protected function requestData() {
    $result = $this->silverPop->exportList(
      $this->getGroupIdentifier(),
      $this->getStartTimeStamp(),
      $this->getEndTimeStamp()
    );
    $this->setRetrievalParameters(array(
      'jobId' => (string) $result['jobId'],
      'filePath' => (string) $result['filePath'],
    ));
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return array(
      'endpoint' => 'https://api4.silverpop.com',
      'statuses' => array('SENT', 'SENDING'),
      'includeZeroSent' => FALSE,
      'includeTest' => FALSE,
      'startTimeStamp' => strtotime('1 week ago'),
      'endTimeStamp' => strtotime('now'),
    );
  }

}
