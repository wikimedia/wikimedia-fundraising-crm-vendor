<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\RecipientsResponse;

class RawRecipientDataExportRequest extends SilverpopBaseRequest
{

  protected $retrievalParameters;

  /**
   * @var string The field that maps to the contact reference.
   *
   *   In silverpop this is a totally custom field so it could be 'anything'.
   *   We use ContactID as a default, but if you use something different you
   *   need to set the field name.
   */
  protected $contactReferenceField;

  /**
   * @return string
   */
  public function getContactReferenceField() {
    return empty($this->contactReferenceField) ? 'ContactID' : $this->contactReferenceField;
  }

  /**
   * @param string $contactReferenceField
   */
  public function setContactReferenceField($contactReferenceField) {
    $this->contactReferenceField = $contactReferenceField;
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
   * @var string
   */
  protected $mailingIdentifier;

  /**
   * @return string
   */
  public function getMailingIdentifier() {
    return $this->mailingIdentifier;
  }

  /**
   * @param string $mailingIdentifier
   */
  public function setMailingIdentifier($mailingIdentifier) {
    $this->mailingIdentifier = $mailingIdentifier;
  }

  /**
   * Get Response
   *
   * @return RecipientsResponse
   */
  public function getResponse() {

    if (!$this->getRetrievalParameters()) {
      $this->requestData();
    }
    $response = new RecipientsResponse(array());
    $response->setRetrievalParameters($this->getRetrievalParameters());
    $response->setSilverpop($this->silverPop);
    $response->setOffset($this->getOffset());
    $response->setContactReferenceField($this->getContactReferenceField());
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
