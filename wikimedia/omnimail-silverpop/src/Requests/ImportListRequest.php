<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Silverpop\Responses\ImportListResponse;

/**
 * Initiate an Acoustic import with a mapping xml file and a csv file.
 *
 * @see https://developer.goacoustic.com/acoustic-campaign/reference/importlist
 */
class ImportListRequest extends SilverpopBaseRequest
{

    /**
     * The csv file to upload.
     *
     * @var string
     */
    protected string $csvFile;

    /**
     * The xml mapping file.
     *
     * @var string
     */
    protected string $xmlFile;

    /**
     * Directory in which to create a file to denote completion.
     *
     * @var null|string
     */
    protected $resultDirectory = NULL;

    /**
     * Are the files already uploaded.
     *
     * @var bool
     */
    protected $isAlreadyUploaded = FALSE;

    public function isAlreadyUploaded(): bool {
        return $this->isAlreadyUploaded;
    }

    public function setIsAlreadyUploaded(bool $isAlreadyUploaded): ImportListRequest {
        $this->isAlreadyUploaded = $isAlreadyUploaded;
        return $this;
    }

    public function getResultDirectory(): ?string {
        return $this->resultDirectory;
    }

    public function setResultDirectory(?string $resultDirectory): ImportListRequest {
        $this->resultDirectory = $resultDirectory;
        return $this;
    }

    public function getXmlFile(): string {
        return $this->xmlFile;
    }

    public function getXmlFileName(): string {
        return basename($this->xmlFile);
    }

    public function setXmlFile(string $xmlFile): ImportListRequest {
        $this->xmlFile = $xmlFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCsvFile() {
        return $this->csvFile;
    }

    public function getCsvFileName(): string {
        return basename($this->csvFile);
    }

    /**
     * @param mixed $csvFile
     *
     * @return ImportListRequest
     */
    public function setCsvFile($csvFile) {
        $this->csvFile = $csvFile;
        return $this;
    }

    /**
     * Get Response
     *
     * @return GroupMembersResponse
     */
    public function getResponse() {
        if (!$this->isAlreadyUploaded()) {
            $this->silverPop->uploadFile($this->getXmlFileName(), $this->getXmlFile(), $this->getResultDirectory());
            $this->silverPop->uploadFile($this->getCsvFileName(), basename($this->getCsvFile()), $this->getResultDirectory());
        }

        $result = $this->silverPop->importList(
            $this->getXmlFileName(),
            $this->getCsvFileName()
        );
        $response = new ImportListResponse();
        $response->setJobId((int) $result['jobId']);
        $response->setIsSuccess(((string) $result['result']) === 'TRUE');
        $response->setSilverpop($this->silverPop);
        return $response;
    }

}
