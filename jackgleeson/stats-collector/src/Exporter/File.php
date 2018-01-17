<?php

namespace Statistics\Exporter;

use Statistics\Helper\TypeHelper;
use Statistics\Collector\iCollector;

/**
 * Export stat data to text file output.
 *
 * e.g.
 *
 * transactions.mobile=10
 * transactions.other=40
 * transactions.tablet=30
 * transactions.website=20
 *
 * @package Statistics\Exporter
 */
class File implements iExporter
{

    /**
     * Flag to include/exclude compound stat keys along with exported output.
     *
     * @var bool
     */
    public $outputCompoundStatKeys = false;

    /**
     * File extension
     *
     * @var string
     */
    public $extension = '.stats';

    /**
     * Directory where we should write file
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
     * Separator character between namespace and stat value
     *
     * @var string
     */
    public $separator = "=";

    /**
     * @var \Statistics\Helper\TypeHelper
     */
    protected $typeHelper;

    /**
     * @param string $path
     * @param string $filename
     */
    public function __construct($filename = "output", $path = ".")
    {
        $this->filename = $filename;
        $this->path = $path;
        $this->typeHelper = new TypeHelper();
    }

    /**
     *
     * Enable Compound stat keys output.
     *
     * Compound stat keys output is disabled by default.
     *
     * (Disabled)
     * users.age=23
     * users.age=12
     * users.age=74
     * users.age=49
     * users.age=9
     *
     * (Enabled)
     * users.age[0]=23
     * users.age[1]=12
     * users.age[2]=74
     * users.age[3]=49
     * users.age[4]=9
     */
    public function enableCompoundStatKeysInOutput()
    {
        $this->outputCompoundStatKeys = true;
    }

    /**
     * Disable Compound stat keys output.
     */
    public function disableCompoundStatKeysInOutput()
    {
        $this->outputCompoundStatKeys = false;
    }


    /**
     * Transform array of statistical data into output data and write to file.
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
        $this->writeStatisticsToFile($output);
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
        foreach ($statistics as $namespace => $stats) {
            if ($this->typeHelper->isCompoundStat($stats)) {
                foreach ($stats as $key => $stat) {
                    if ($this->outputCompoundStatKeys === false) {
                        $contents[] = $this->mapStatToLine($namespace, $stat);
                    } else {
                        $contents[] = $this->mapStatToLine($namespace . "[" . $key . "]", $stat);
                    }
                }
            } else {
                $contents[] = $this->mapStatToLine($namespace, $stats);
            }
        }

        //convert array to output string
        $strOutput = implode('', $contents);
        return $strOutput;
    }

    /**
     * Write output file
     *
     * @param $output
     */
    protected function writeStatisticsToFile($output)
    {
        $outputPath = $this->path . DIRECTORY_SEPARATOR . $this->filename . $this->extension;
        file_put_contents($outputPath, $output);
    }

    /**
     * @param string $namespace
     * @param mixed $stat
     *
     * @return string
     */
    protected function mapStatToLine($namespace, $stat)
    {
        return $namespace . $this->separator . $stat . PHP_EOL;
    }
}