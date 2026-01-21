<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Responses;

use League\Csv\Reader;
use League\Csv\Statement;
use Omnimail\Silverpop\Downloader;

class DownloadResponse extends BaseResponse {

    use Downloader;

    /**
     * Parameters for retrieving the results.
     *
     * @var array
     */
    protected $retrievalParameters;

    protected $downloadDirectory;

    protected ?string $sftpUrl;

    public function getSftpUrl(): ?string {
        return $this->sftpUrl;
    }

    public function setSftpUrl(?string $sftpUrl): self {
        $this->sftpUrl = $sftpUrl;
        return $this;
    }

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

    /**
     * @return \League\Csv\ResultSet
     * @throws \League\Csv\Exception
     */
    public function getData() {
        $this->setCsvReader();
        $stmt = (new Statement())->offset($this->getOffset());

        if ($this->getLimit()) {
            $stmt->limit($this->getLimit());
        }

        return $stmt->process($this->reader);
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
     * @return array
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
