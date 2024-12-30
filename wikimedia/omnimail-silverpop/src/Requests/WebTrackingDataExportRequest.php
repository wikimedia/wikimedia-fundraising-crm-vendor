<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\WebTrackingResponse;

class WebTrackingDataExportRequest extends SilverpopBaseRequest {

  protected ?int $databaseID = NULL;

  public function getDatabaseID(): ?int {
    return $this->databaseID;
  }

  public function setDatabaseID(?int $databaseID): WebTrackingDataExportRequest {
    $this->databaseID = $databaseID;
    return $this;
  }

  public function getSites(): array {
    return $this->sites;
  }

  public function setSites(array $sites): WebTrackingDataExportRequest {
    $this->sites = $sites;
    return $this;
  }

  public function getDomains(): array {
    return $this->domains;
  }

    public function setDomains(array $domains): WebTrackingDataExportRequest {
        $this->domains = $domains;
        return $this;
    }

  public function getActions(): array {
    return $this->actions;
  }

  public function setActions(array $actions): WebTrackingDataExportRequest {
    $this->actions = $actions;
    return $this;
  }

  protected array $sites = [];

  protected array $domains = [];

  protected array $actions = [];

  protected array $retrievalParameters = [];

  /**
   * @return array
   */
  public function getRetrievalParameters(): array {
    return $this->retrievalParameters;
  }

  /**
   * @param array $retrievalParameters
   *
   * @return \Omnimail\Silverpop\Requests\WebTrackingDataExportRequest
   */
  public function setRetrievalParameters(array $retrievalParameters): self {
    $this->retrievalParameters = $retrievalParameters;
    return $this;
  }

  /**
   * Get Response
   *
   * @return WebTrackingResponse
   * @throws \SilverpopConnector\SilverpopConnectorException
   */
  public function getResponse() {

    if (!$this->getRetrievalParameters()) {
      $this->requestData();
    }
    $response = new WebTrackingResponse();
    $response->setRetrievalParameters($this->getRetrievalParameters());
    $response->setSilverpop($this->silverPop);
    $response->setOffset($this->getOffset());
    return $response;
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters(): array {
    return [
      'endpoint' => 'https://api-campaign-us-4.goacoustic.com/XMLAPI',
      'sftpEndpoint' => 'transfer4.silverpop.com',
      'startTimeStamp' => strtotime('1 week ago'),
      'endTimeStamp' => strtotime('now'),
    ];
  }

  /**
   * Request data from the provider.
   *
   * @throws \SilverpopConnector\SilverpopConnectorException
   */
  protected function requestData(): void {
    $dates = [];
    if ($this->getStartTimeStamp()) {
      $dates['EVENT_DATE_START'] = date('Y-m-d H:i:s', $this->getStartTimeStamp());
    }
    if ($this->getEndTimeStamp()) {
      $dates['EVENT_DATE_END'] = date('Y-m-d H:i:s', $this->getEndTimeStamp());
    }
    $result = $this->silverPop->webTrackingDataExport(
      $dates,
      ['MOVE_TO_FTP'] + $this->getActions(),
      ['DATABASE_ID' => $this->getCredential('database_id')],
      $this->getSites(),
      $this->getDomains(),
      ['ContactID', 'Email']
    );
    $this->setRetrievalParameters([
      'jobId' => (string) $result->Body->RESULT->JOB_ID,
      'filePath' => (string) $result->Body->RESULT->FILE_PATH,
    ]);
  }

}
