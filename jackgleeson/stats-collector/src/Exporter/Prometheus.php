<?php

namespace Statistics\Exporter;

use Statistics\Collector\iCollector;
use Statistics\Helper\TypeHelper;

/**
 * Export stat data to match Prometheus file format.
 *
 * e.g.
 *
 * transactions_mobile 10
 * transactions_other 40
 * transactions_tablet 30
 * transactions_website 20
 *
 * @package Statistics\Exporter
 */
class Prometheus implements iExporter
{

    /**
     * Regular expression pattern to validate Prometheus labels.
     *
     * Taken from https://prometheus.io/docs/concepts/data_model/#metric-names-and-labels
     */
    const LABEL_PATTERN = "/^([a-z_]+)=([a-z0-9_]+)$/i";

    /**
     * Prometheus files extension
     *
     * @var string
     */
    public $extension = '.prom';

    /**
     * Directory where we should write Prometheus files
     *
     * @var string $path
     */
    public $path;

    /**
     * Filename to write statistics out to
     *
     * @var string
     */
    public $filename;

    /**
     * @var \Statistics\Helper\TypeHelper
     */
    protected $typeHelper;

    /**
     * @param string $path
     * @param string $filename
     */
    public function __construct($filename = "prometheus", $path = ".")
    {
        $this->filename = $filename;
        $this->path = $path;
        $this->typeHelper = new TypeHelper();
    }

    /**
     * Transform array of statistical data into Prometheus metrics output and
     * write to file.
     *
     * Take either an array of key=>value statistical data or an instance of
     * iCollector.
     *
     * @param array|iCollector $statistics
     *
     * @return bool
     */
    public function export($statistics)
    {
        if ($statistics instanceof iCollector) {
            $statistics = $statistics->getAllStats();
        }
        $output = $this->mapStatisticsToOutput($statistics);
        $this->writeStatisticsToPrometheusFile($output);
        return true;
    }

    /**
     * @param $statistics
     *
     * @return string
     */
    protected function mapStatisticsToOutput($statistics)
    {
        $contents = [];
        foreach ($statistics as $subject => $stats) {
            $subject = $this->mapDotsToUnderscore($subject);
            if ($this->typeHelper->isCompoundStat($stats)) {
                foreach ($stats as $key => $stat) {
                    if ($this->isMetricWithLabelAsKey($key)) {
                        $contents[] = $this->mapToMetricLabelLineOutput($subject, $key, $stat);
                    } else {
                        $contents[] = $this->mapToMetricLineOutput($subject, $stat);
                    }
                }
            } else {
                $contents[] = $this->mapToMetricLineOutput($subject, $stats);
            }
        }

        //convert array to string output
        $strOutput = implode('', $contents);
        return $strOutput;
    }

    /**
     * Write output file
     *
     * @param $output
     */
    protected function writeStatisticsToPrometheusFile($output)
    {
        $outputPath = $this->path . DIRECTORY_SEPARATOR . $this->filename . $this->extension;
        file_put_contents($outputPath, $output);
    }

    /**
     * Check to see if a we should create a metric label based on the contents of the statistic array key
     *
     * @param $key
     *
     * @return bool
     */
    private function isMetricWithLabelAsKey($key)
    {
        return (preg_match(static::LABEL_PATTERN, $key) === 1);
    }

    /**
     * Map non-numeric strings to a metric label
     *
     * Currently only supports one label per metric.
     *
     * @param string $subject
     * @param string $key
     * @param mixed $stat
     *
     * @return string
     */
    private function mapToMetricLabelLineOutput($subject, $key, $stat)
    {
        preg_match(static::LABEL_PATTERN, $key, $matches);
        return $subject . "{" . $matches[1] . "=\"" . $matches[2] . "\"} " . $stat . PHP_EOL;
    }

    /**
     * @param string $subject
     * @param mixed $stat
     *
     * @return string
     */
    private function mapToMetricLineOutput($subject, $stat)
    {
        return $subject . " " . $stat . PHP_EOL;
    }

    /**
     * @param $input
     *
     * @return mixed
     */
    private function mapDotsToUnderscore($input)
    {
        return str_replace(".", "_", $input);
    }

}