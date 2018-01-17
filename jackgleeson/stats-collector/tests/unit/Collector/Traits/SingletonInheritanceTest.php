<?php

require('TestCollector.php');

/**
 * @covers \Statistics\Collector\Traits\SingletonInheritance
 */
class SingletonInheritanceTest extends \PHPUnit\Framework\TestCase
{

    public function testCanCreateSingletonInstance()
    {
        $statsCollector = Statistics\Collector\Collector::getInstance();
        $this->assertInstanceOf(Statistics\Collector\Collector::class, $statsCollector);
    }

    /**
     * TestCollector extends Statistics\Collector\Collector
     */
    public function testCanCreateExtendingChildInstance()
    {
        $testStatsCollector = TestCollector::getInstance();
        $this->assertInstanceOf(TestCollector::class, $testStatsCollector);
        $this->assertInstanceOf(Statistics\Collector\Collector::class, $testStatsCollector);
    }

    public function testCanTearDownAllSingletonInstances()
    {
        //open up access to $Statistics\Collector\Collector::instances[]
        $reflectionProperty = new \ReflectionProperty(Statistics\Collector\Collector::class, "instances");
        $reflectionProperty->setAccessible(true);

        $statsCollector = Statistics\Collector\Collector::getInstance();
        $this->assertNotEmpty($reflectionProperty->getValue($statsCollector));

        $statsCollector::tearDown(true);
        $this->assertEmpty($reflectionProperty->getValue($statsCollector));
    }

    public function tearDown()
    {
        Statistics\Collector\AbstractCollector::tearDown(true);
        parent::tearDown();
    }

}