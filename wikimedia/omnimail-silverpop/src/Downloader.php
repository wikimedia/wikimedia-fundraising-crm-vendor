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
    if (
      // We Cannot find the csv or original file
      (!file_exists($filePath) && !file_exists($csvFilePath))
      ||
      // Or we cannot find the file that marks the download has completed.
      // Note this originally checked for the csv file - I think it should check
      // for the other INSTEAD but have made it as well out of caution.
      (!file_exists($csvFilePath . '.complete') && !file_exists($filePath . '.complete'))
    ) {
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
