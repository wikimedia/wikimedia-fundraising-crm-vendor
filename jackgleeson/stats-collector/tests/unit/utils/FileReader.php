<?php

/**
 * Class FileReader
 *
 * Utility helper to read in stats data from file for tests
 */
class FileReader
{

    const STANDARD_FILE_STAT_DELIMITER = "=";

    const PROMETHEUS_FILE_STAT_DELIMITER = " ";

    public static function buildArrayFromOutputFile($fileLocation)
    {
        $statsWrittenAssocArray = [];
        if (file_exists($fileLocation)) {
            $statsFileFullPath = $fileLocation;
            $statsWritten = rtrim(file_get_contents($statsFileFullPath)); // remove trailing \n
            $statsWrittenLinesArray = explode("\n", $statsWritten);
            foreach ($statsWrittenLinesArray as $statsLine) {
                list($name, $value) = explode(self::STANDARD_FILE_STAT_DELIMITER, $statsLine);
                if (array_key_exists($name, $statsWrittenAssocArray)) {
                    if (is_array($statsWrittenAssocArray[$name])) {
                        $statsWrittenAssocArray[$name][] = $value;
                    } else {
                        $statsWrittenAssocArray[$name] = [$statsWrittenAssocArray[$name], $value];
                    }
                } else {
                    $statsWrittenAssocArray[$name] = $value;
                }
            }
        } else {
            return "File does not exist";
        }

        return $statsWrittenAssocArray;
    }

    public static function buildArrayFromPrometheusOutputFile($prometheusFileLocation)
    {
        $statsWrittenAssocArray = [];
        if (file_exists($prometheusFileLocation)) {
            $statsFileFullPath = $prometheusFileLocation;
            $statsWritten = rtrim(file_get_contents($statsFileFullPath)); // remove trailing \n
            $statsWrittenLinesArray = explode("\n", $statsWritten);
            foreach ($statsWrittenLinesArray as $statsLine) {
                list($name, $value) = explode(self::PROMETHEUS_FILE_STAT_DELIMITER, $statsLine);
                if (array_key_exists($name, $statsWrittenAssocArray)) {
                    if (is_array($statsWrittenAssocArray[$name])) {
                        $statsWrittenAssocArray[$name][] = $value;
                    } else {
                        $statsWrittenAssocArray[$name] = [$statsWrittenAssocArray[$name], $value];
                    }
                } else {
                    $statsWrittenAssocArray[$name] = $value;
                }
            }
        } else {
            return "Prometheus file does not exist";
        }

        return $statsWrittenAssocArray;
    }
}