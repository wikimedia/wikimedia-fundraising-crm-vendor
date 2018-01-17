<?php

require_once('tests/unit/utils/FileReader.php');

/**
 * @covers \Statistics\Exporter\File<extended>
 * @covers \Statistics\Collector\Collector<extended>
 * @covers \Statistics\Collector\Traits\SingletonInheritance
 */
class FileTest extends \PHPUnit\Framework\TestCase
{

    const DELIMITER = "=";

    protected $filename;

    protected $filePath;

    protected $fileExtension;

    public function testExporterImplementsExporterInterface()
    {
        $exporter = new Statistics\Exporter\File();

        $this->assertInstanceOf(Statistics\Exporter\iExporter::class, $exporter);
    }

    public function testExportCreatesFile()
    {
        $this->setupTmpStatsFileProperties();
        $fileLocation = $this->filePath . DIRECTORY_SEPARATOR . $this->filename . $this->fileExtension;

        // confirm file doesn't exist before export
        $this->assertFileNotExists($fileLocation);

        $statsCollector = $this->getTestStatsCollectorInstance();
        $statsCollector->addStat("test", 1);

        $exporter = new Statistics\Exporter\File($this->filename, $this->filePath);
        $exporter->export($statsCollector);

        // confirm file now exists after export
        $this->assertFileExists($fileLocation);

        //clean up
        $this->removeTmpFile($fileLocation);
        $this->removeTmpDir($this->filePath);
    }

    public function testExportOutputsValidStats()
    {
        $this->setupTmpStatsFileProperties();
        $fileLocation = $this->filePath . DIRECTORY_SEPARATOR . $this->filename . $this->fileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("milky_way");
        $statsCollector->addStat("planets", 100000000000);
        $statsCollector->addStat("stars", 400000000000);
        $statsCollector->addStat("age_in_years", 13800000000);

        $exporter = new Statistics\Exporter\File($this->filename, $this->filePath);
        $exporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromOutputFile($fileLocation);

        $expectedOutput = [
          'milky_way.planets' => 100000000000,
          'milky_way.stars' => 400000000000,
          'milky_way.age_in_years' => 13800000000,
        ];

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($fileLocation);
        $this->removeTmpDir($this->filePath);
    }

    public function testExportOutputsValidCompoundStats()
    {
        $this->setupTmpStatsFileProperties();
        $fileLocation = $this->filePath . DIRECTORY_SEPARATOR . $this->filename . $this->fileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("test");
        $statsCollector->addStat("ages", [19, 32, 44, 60, 54, 67]);

        $exporter = new Statistics\Exporter\File($this->filename, $this->filePath);
        $exporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromOutputFile($fileLocation);


        $expectedOutput = [
          'test.ages' => [19, 32, 44, 60, 54, 67],
        ];

        /*
         * the actual file output for this looks like:
         * test.ages=19
         * test.ages=32
         * test.ages=44
         * ...
         *
         * but due to not being able to have the same key multiple times in php arrays, we end up when mapping back
         * to a php array with:
         * test.ages=[19,32,44,..]
         */

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($fileLocation);
        $this->removeTmpDir($this->filePath);
    }

    public function testExportOutputsValidCompoundStatsWithKeysIfEnabled()
    {
        $this->setupTmpStatsFileProperties();
        $fileLocation = $this->filePath . DIRECTORY_SEPARATOR . $this->filename . $this->fileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("test");

        $statsCollector->addStat("ages", [
          "jack" => 19,
          "joe" => 32,
          "bob" => 44,
          "alice" => 60,
        ]);

        $exporter = new Statistics\Exporter\File($this->filename, $this->filePath);
        $exporter->enableCompoundStatKeysInOutput(); //enable the compound stat keys output
        $exporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromOutputFile($fileLocation);

        $expectedOutput = [
          'test.ages[jack]' => '19',
          'test.ages[joe]' => '32',
          'test.ages[bob]' => '44',
          'test.ages[alice]' => '60',
        ];

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($fileLocation);
        $this->removeTmpDir($this->filePath);
    }

    public function testExportOutputsValidCompoundStatsWithKeysIfEnabledThenDisabled()
    {
        $this->setupTmpStatsFileProperties();
        $fileLocation = $this->filePath . DIRECTORY_SEPARATOR . $this->filename . $this->fileExtension;

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $statsCollector->setNamespace("test");

        $statsCollector->addStat("ages", [
          "jack" => 19,
          "joe" => 32,
          "bob" => 44,
          "alice" => 60,
        ]);

        $exporter = new Statistics\Exporter\File($this->filename, $this->filePath);
        $exporter->enableCompoundStatKeysInOutput(); //enable the compound stat keys output
        $exporter->disableCompoundStatKeysInOutput(); //disable the compound stat keys output resetting it back to default
        $exporter->export($statsCollector);

        $statsOutputToAssocArray = FileReader::buildArrayFromOutputFile($fileLocation);

        $expectedOutput = [
          'test.ages' => [19, 32, 44, 60],
        ];

        $this->assertEquals($expectedOutput, $statsOutputToAssocArray);

        //clean up
        $this->removeTmpFile($fileLocation);
        $this->removeTmpDir($this->filePath);
    }

    public function tearDown()
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
        $this->filename = $filename;
        $this->filePath = $this->createTmpDir();
        $this->fileExtension = ".stats";
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
