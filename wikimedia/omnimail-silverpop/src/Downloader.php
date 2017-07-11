<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop;

trait Downloader {

  /**
   * Download a file from the sftp server.
   *
   * @param string $fileName
   * @param string $destination
   *   Full path of where to save it to.
   *
   * Sample code:
   *
   * $status = $this->silverPop->getJobStatus($this->getJobStatus();
   *   if ($status === 'COMPLETE')) {
   *     $file = $result->downloadFile();
   *   }
   *
   * @return bool
   */
  public function downloadFile($fileName, $destination) {
    $silverPop =  $this->getSilverpop();
    $silverPop->downloadFile($fileName, $destination);
  }

  public function downloadCsv() {
    $filePath = $this->getDownloadDirectory() . '/' . $this->getFileName();
    $csvFilePath = str_replace('.zip', '.csv', $filePath);
    if ((!file_exists($filePath) && !file_exists($csvFilePath)) || !file_exists($csvFilePath . '.complete')) {
      $this->downloadFile($this->getFileName(), $filePath);
    }
    if (!file_exists($csvFilePath) && substr($filePath, -4) === '.zip') {
      $zip = new \ZipArchive;
      if ($zip->open($filePath) === TRUE) {
        $zip->extractTo($this->getDownloadDirectory());
        $zip->close();
        unlink($filePath);
      }
    }
    return $csvFilePath;
  }

}
