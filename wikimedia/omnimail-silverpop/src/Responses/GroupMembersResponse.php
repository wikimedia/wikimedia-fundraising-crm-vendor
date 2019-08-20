<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

use phpseclib\Net\Sftp;
use League\Csv\Reader;
use League\Csv\Statement;
use Omnimail\Silverpop\Responses\Contact;

class GroupMembersResponse extends BaseResponse
{
  use \Omnimail\Silverpop\Downloader;

  /**
   * Parameters for retrieving the results.
   *
   * @var array
   */
  protected $retrievalParameters;

  protected $downloadDirectory;

  /**
   * @var Reader
   */
  protected $reader;

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
   * Get Csv Reader object.
   *
   * @return \League\Csv\Reader
   *
   * @throws \League\Csv\Exception
   */
  public function getReader() {
    if (!$this->reader) {
      $this->reader = Reader::createFromPath($this->downloadCsv());
      $this->reader->setHeaderOffset(0);
    }
    return $this->reader;
  }

  /**
   * @param \League\Csv\Reader $reader
   */
  public function setReader($reader) {
    $this->reader = $reader;
  }

  /**
   * @return mixed
   */
  public function getDownloadDirectory() {
    if (!$this->downloadDirectory) {
      $this->downloadDirectory = sys_get_temp_dir();
    }
    return $this->downloadDirectory;
  }

  /**
   * @param mixed $downloadDirectory
   */
  public function setDownloadDirectory($downloadDirectory) {
    $this->downloadDirectory = $downloadDirectory;
  }

  /**
   * @return array
   */
  public function getRetrievalParameters() {
    return $this->retrievalParameters;
  }

  /**
   * Get the filename.
   *
   * Silverpop inconsistently adds the folder, strip it here.
   */
  protected function getFileName() {
    $filePath = (string) $this->retrievalParameters['filePath'];
    $filePathParts = explode('/', $filePath);
    return array_pop($filePathParts);
  }

  protected function getJobStatus() {
    return $this->retrievalParameters['jobId'];
  }

  public function isCompleted() {
    $status = $this->silverPop->getJobStatus($this->getJobStatus());
    return ($status === 'COMPLETE');
  }

    /**
     * @return \League\Csv\ResultSet
     *
     * @throws \League\Csv\Exception
     */
  public function getData() {
    $stmt = (new Statement())->offset($this->getOffset());

    if ($this->getLimit()) {
      $stmt->limit($this->getLimit());
    }

    return $stmt->process($this->getReader());
  }

  public function isRetrievalRequired() {
    return TRUE;
  }

  /**
   * @param array $retrievalParameters
   */
  public function setRetrievalParameters($retrievalParameters) {
    $this->retrievalParameters = $retrievalParameters;
  }

  /**
   * @throws \League\Csv\Exception
   */
  public function setCsvReader() {
    $csvFile = $this->downloadCsv();
    $this->reader = Reader::createFromPath($csvFile);
    $this->reader->setHeaderOffset(0);
  }

    /**
     * @return mixed
     *
     * @throws \League\Csv\Exception
     */
  public function getCsvColumns() {
    if (!$this->reader) {
      $this->setCsvReader();
    }
    return $this->reader->getHeader();
  }

}
