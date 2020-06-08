<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\QueryCriteriaResponse;

class GetQueryRequest extends SilverpopBaseRequest
{
  /**
   * @var string
   */
  protected $queryIdentifier;

  /**
   * @return string
   */
  public function getQueryIdentifier() {
    return $this->queryIdentifier;
  }

  /**
   * @param string $queryIdentifier
   */
  public function setQueryIdentifier($queryIdentifier) {
    $this->queryIdentifier = $queryIdentifier;
  }

  /**
   * Get Response
   *
   * @return \Omnimail\Silverpop\Responses\QueryCriteriaResponse
   */
  public function getResponse() {
    $data = $this->silverPop->getQuery(['listId' => $this->getQueryIdentifier()]);
    $response = new QueryCriteriaResponse($data);
    return $response;
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return array(
      'endpoint' => 'https://api4.silverpop.com',
      'statuses' => array('sent', 'sending'),
      'includeZeroSent' => FALSE,
      'includeTest' => FALSE,
      'startTimeStamp' => strtotime('1 week ago'),
      'endTimeStamp' => strtotime('now'),
    );
  }

  /**
   * Request data from the provider.
   */
  protected function requestData() {
    $dates = array();
    if ($this->getStartTimeStamp()) {
      $dates['EVENT_DATE_START'] = date('m/d/Y H:i:s', $this->getStartTimeStamp());
    }
    if ($this->getEndTimeStamp()) {
      $dates['EVENT_DATE_END'] = date('m/d/Y H:i:s', $this->getEndTimeStamp());
    }
    $result = $this->silverPop->rawRecipientDataExport(
      $this->getMailingIdentifier(),
      "MAILING_ID",
      $dates,
      NULL,
      array('MOVE_TO_FTP' => TRUE, 'ALL_EVENT_TYPES' => TRUE),
      array('ContactID' => 'ContactID')
    );
    $this->setRetrievalParameters(array(
      'jobId' => (string) $result->Body->RESULT->MAILING->JOB_ID,
      'filePath' => (string) $result->Body->RESULT->MAILING->FILE_PATH,
    ));
  }

}
