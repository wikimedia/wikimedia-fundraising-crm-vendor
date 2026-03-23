<?php

require_once('tests/unit/utils/FileReader.php');

/**
 * @covers \Statistics\Exporter\Prometheus<extended>
 * @covers \Statistics\Collector\Collector<extended>
 * @covers \Statistics\Collector\Traits\SingletonInheritance
 */
class PrometheusTest extends \PHPUnit\Framework\TestCase
{

    const DELIMITER = " ";

    protected $promFilename;

    protected $promFilePath;

    protected $promFileExtension;

    public function testPrometheusExporterImplementsExporterInterface()
    {
        $prometheusExporter = new Statistics\Exporter\Prometheus();

        $this->assertInstanceOf(Statistics\Exporter\iExporter::class, $prometheusExporter);
    }

    public function testExportCreatesPrometheusFile()
    {
        $this->setupTmpStatsFileProperties();
        $promFileLocation = $this->promFilePath . DIRECTORY_SEPARATOR . $this->promFilename . $this->promFileExtension;

        // confirm file doesn't exist before export
        $this->assertFileDoesNotExist($promFileLocation);

        $statsCollector = $this->getTestStatsCollectorInstance();
        $statsCollector->addStat("test", 1);

        $prometheusExporter = new Statistics\Exporter\Prometheus($this->promFilename, $this->promFilePath);
        $prometheusExporter->export($statsCollector);

        // confirm file now exists after export
        $this->assertFileExists($promFileLocation);

        //clean up
        $this->removeTmpFile($promFileLocation);
        $this->removeTmpDir($this->promFilePath);
    }

    public function testExportMapsNamespaceDotsToUnderscores()
    {
        $this->setupTmpStatsFileProperties();
        $promFileLocation = $this->promFilePath . DIRECTORY_SEPARATOR . $this->promFilename . $this->promFileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("this.is.a.really.long.namespace");
        $statsCollector->addStat("pi", 3.14159265359);

        $prometheusExporter = new Statistics\Exporter\Prometheus($this->promFilename, $this->promFilePath);
        $prometheusExporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromPrometheusOutputFile($promFileLocation);
        $expectedStatName = 'this_is_a_really_long_namespace_pi';

        $this->assertArrayHasKey($expectedStatName, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($promFileLocation);
        $this->removeTmpDir($this->promFilePath);
    }

    public function testExportOutputsValidStats()
    {
        $this->setupTmpStatsFileProperties();
        $promFileLocation = $this->promFilePath . DIRECTORY_SEPARATOR . $this->promFilename . $this->promFileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("milky_way");
        $statsCollector->addStat("planets", 100000000000);
        $statsCollector->addStat("stars", 400000000000);
        $statsCollector->addStat("age_in_years", 13800000000);

        $prometheusExporter = new Statistics\Exporter\Prometheus($this->promFilename, $this->promFilePath);
        $prometheusExporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromPrometheusOutputFile($promFileLocation);

        $expectedOutput = [
          'milky_way_planets' => 100000000000,
          'milky_way_stars' => 400000000000,
          'milky_way_age_in_years' => 13800000000,
        ];

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($promFileLocation);
        $this->removeTmpDir($this->promFilePath);
    }

    /*
     * TODO:
     * This test highlights that we are allowing *broken* output. Prometheus does not allow non-unique metric keys
     * in the output. To fix this, the actual output needs validating in the exporter and then this test can instead
     * check that we only output unique metric keys when a compound stat is encountered during the export.
     */
    public function testExportOutputsValidCompoundStats()
    {
        $this->setupTmpStatsFileProperties();
        $promFileLocation = $this->promFilePath . DIRECTORY_SEPARATOR . $this->promFilename . $this->promFileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("observer");
        $statsCollector->addStat("ages", [19, 32, 44, 60, 54, 67]);

        $prometheusExporter = new Statistics\Exporter\Prometheus($this->promFilename, $this->promFilePath);
        $prometheusExporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromPrometheusOutputFile($promFileLocation);

        $expectedOutput = [
          'observer_ages' => [19, 32, 44, 60, 54, 67],
        ];

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($promFileLocation);
        $this->removeTmpDir($this->promFilePath);
    }

    public function testExportConvertsLabelFormatArrayKeyToValidPrometheusLabels()
    {
        $this->setupTmpStatsFileProperties();
        $promFileLocation = $this->promFilePath . DIRECTORY_SEPARATOR . $this->promFilename . $this->promFileExtension;

        $data = [
          'type=mouse' => 'Mickey Mouse',
          'type=_dog_' => 'Pluto',
          'total' => 2,
        ];

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("labels_test");
        $statsCollector->addStat("characters", $data);

        $prometheusExporter = new Statistics\Exporter\Prometheus($this->promFilename, $this->promFilePath);
        $prometheusExporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromPrometheusOutputFile($promFileLocation);

        $expectedOutputWithLabels = [
          'labels_test_characters{type="mouse"}' => 'Mickey',
          'labels_test_characters{type="_dog_"}' => 'Pluto',
          'labels_test_characters' => '2',
        ];

        $this->assertEquals($expectedOutputWithLabels, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($promFileLocation);
        $this->removeTmpDir($this->promFilePath);
    }

    public function tearDown(): void
    {
        Statistics\Collector\Collector::tearDown(true);
    }

    private function getTestStatsCollectorInstance()
    {
        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("test_namespace");
        return $statsCollector;
    }

    private function setupTmpStatsFileProperties($filename = "test_stats")
    {
        $this->promFilename = $filename;
        $this->promFilePath = $this->createTmpDir();
        $this->promFileExtension = '.prom';
    }

    private function createTmpDir()
    {
        $tempfile = tempnam(sys_get_temp_dir(), 'tmp_');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
    }

    private function removeTmpDir($tmpDir)
    {
        if (is_dir($tmpDir)) {
            rmdir($tmpDir);
        }
    }

    private function removeTmpFile($tmpFile)
    {
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }
}
