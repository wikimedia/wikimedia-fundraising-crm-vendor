<?php

namespace Statistics\Exporter;

use Statistics\Collector\iCollector;

/**
 * Write out metrics in a Prometheus-readable format.
 */
class Prometheus implements iExporter
{

    /**
     * Prometheus files extension
     *
     * @var string
     */
    public static $extension = '.prom';

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
     * @param string $path
     * @param string $filename
     */
    public function __construct($filename = "prometheus", $path = ".")
    {
        $this->filename = $filename;
        $this->path = $path;
    }


    /**
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
        $this->writeStatisticsToPrometheusFile($statistics);
        return true;
    }

    /**
     * Transform array of statistical data into Prometheus metrics output and
     * write to file.
     *
     * @param array $statistics
     */
    protected function writeStatisticsToPrometheusFile(array $statistics)
    {
        $contents=[];
        foreach ($statistics as $subject => $stats) {
            $subject = $this->mapDotsToUnderscore($subject);

            if (is_array($stats)) {
                foreach ($stats as $stat) {
                    $contents[] = "$subject $stat\n";
                }
            } else {
                $contents[] = "$subject $stats\n";
            }
        }

        file_put_contents($this->path . DIRECTORY_SEPARATOR . $this->filename . self::$extension,
          implode('', $contents));
    }

    private function mapDotsToUnderscore($input)
    {
        return str_replace(".", "_", $input);
    }


}