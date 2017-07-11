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

class RecipientsResponse extends BaseResponse
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
   * @return \League\Csv\Reader
   */
  public function getReader() {
    if (!$this->reader) {
      $this->reader = Reader::createFromPath($this->downloadCsv());
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

  protected function getFileName() {
    return (string) $this->retrievalParameters['filePath'];
  }

  protected function getJobStatus() {
    return $this->retrievalParameters['jobId'];
  }

  public function isCompleted() {
    $status = $this->silverPop->getJobStatus($this->getJobStatus());
    return ($status === 'COMPLETE');
  }

  public function getData() {
    $headerRow = $this->getCsvColumns();

    $filterOutRow = function ($row, $rowIndex) {
      return $rowIndex != 0;
    };
    $this->reader->addFilter($filterOutRow);

    $formatFunction = function ($row) {
      if (isset($row['email'])) {
        $row['email'] = addslashes($row['email']);
      }
      if (isset($row['recipient_action_timestamp'])) {
        $row['recipient_action_timestamp'] = strtotime($row['recipient_action_timestamp']);
      }
      if (isset($row[$this->contactReferenceField])) {
        $row->contactIdentifier = $row[$this->contactReferenceField];
      }
      return new Recipient($row);
    };
    $keys = (array) $this->normalizeKeys($headerRow);
    $results = $this->reader->fetchAssoc($keys, $formatFunction);
    return $results;
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
   * Map keys to a normalised array, intended to be generic of the provider.
   *
   * @param array $csvHeaders
   *
   * @return array
   *   New headers, normalized to something other providers might use.
   */
  public function normalizeKeys($csvHeaders) {
    $newHeaders = array();
    $normalizedKeys = array(
      'Recipient Id' => 'contact_identifier',
      'Mailing Id' => 'mailing_identifier',
      'Campaign Id' => 'campaign_identifier',
      'Event Timestamp' => 'recipient_action_timestamp',
      'Event_Type' => 'receipient_action',
      'Email' => 'email',
    );
    foreach ($csvHeaders as $csvHeader) {
      if (isset($normalizedKeys[$csvHeader])) {
        $newHeaders[] = $normalizedKeys[$csvHeader];
      }
      else {
        $newHeaders[] = strtolower(str_replace(' ', '_', $csvHeader));
      }
    }
    return $newHeaders;
  }

  public function setCsvReader() {
    $csvFile = $this->downloadCsv();
    $this->reader = Reader::createFromPath($csvFile);
  }

  /**
   * @return mixed
   */
  public function getCsvColumns() {
    if (!$this->reader) {
      $this->setCsvReader();  
    } 
    $headerRow = $this->reader->fetchOne();
    return $headerRow;
  }

}
