<?php

use PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;

/**
 * @covers \Statistics\Collector\Traits\SingletonInheritance
 * @covers \Statistics\Collector\Collector<extended>
 */
class CollectorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Statistics\Collector\Collector
     */
    protected $statsCollector;

    public function setUp()
    {
        $this->statsCollector = Statistics\Collector\Collector::getInstance();
        parent::setUp();
    }

    public function testCollectorImplementsAbstractCollector()
    {
        $this->assertInstanceOf(Statistics\Collector\AbstractCollector::class, $this->statsCollector);
    }

    public function testCollectorImplementsCollectorInterface()
    {
        $this->assertInstanceOf(Statistics\Collector\iCollector::class, $this->statsCollector);
    }

    public function testCollectorImplementsSingletonInterface()
    {
        $this->assertInstanceOf(Statistics\Collector\iSingleton::class, $this->statsCollector);
    }

    public function testDefaultRootNamespaceSetInCollectorClass()
    {
        $currentNamespace = $this->statsCollector->getNamespace();

        $this->assertEquals("root", $currentNamespace);
    }

    public function testCanChangeRootNamespace()
    {
        $this->statsCollector->setNamespace("phpunit");

        $currentNamespace = $this->statsCollector->getNamespace();

        $this->assertEquals("phpunit", $currentNamespace);
    }

    public function testCanAddStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("number_of_planets", 8);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals(8, $stats["test_namespace.number_of_planets"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testCanAddStringAsStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("hello", "world");

        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $stats["test_namespace.hello"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testCanAddIntegerAsStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("days_per_year", 365);

        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_INT, $stats["test_namespace.days_per_year"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testCanAddFloatAsStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("pi", 3.14159265359);

        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_FLOAT, $stats["test_namespace.pi"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testCanAddArrayAsStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $fibonacciSequence = [0, 1, 1, 2, 3, 5, 8, 13, 21, 34];
        $this->statsCollector->addStat("fibonacci_sequence", $fibonacciSequence);

        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_ARRAY, $stats["test_namespace.fibonacci_sequence"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testCanAddAssociativeArrayAsStat()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $mathConstants = [
          "pi" => 3.14159265359,
          'golden_ratio' => 1.61803398875,
        ];

        $this->statsCollector->addStat("math_constants", $mathConstants);
        $stats = $this->statsCollector->getAllStats();

        $expected = [
          "pi" => 3.14159265359,
          'golden_ratio' => 1.61803398875,
        ];

        $this->assertInternalType(PHPUnit_IsType::TYPE_ARRAY, $stats["test_namespace.math_constants"]);
        $this->assertEquals($expected, $stats["test_namespace.math_constants"]);
    }

    public function testCanCreateCompoundStatIncrementally()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", 1);
        $this->statsCollector->addStat("compound_stat", 2);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1, 2], $stats["test_namespace.compound_stat"]);
    }

    public function testCanCreateCompoundStatWithArray()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", [1, 2]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1, 2], $stats["test_namespace.compound_stat"]);
    }

    public function testCanCreateCompoundStatWithArrayAsValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", [1 => [2, 3], 2], $options = ['flatten' => false]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1 => [2, 3], 2], $stats["test_namespace.compound_stat"]);
    }

    public function testCanConveryCompoundStatValueIntoCompoundValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", ["planets" => "Earth"]);
        $this->statsCollector->addStat("compound_stat", ["planets" => "Mars"]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals(["planets" => ["Earth", "Mars"]], $stats["test_namespace.compound_stat"]);
    }

    public function testCanRemoveStat()
    {
        //open up access to $Statistics\Collector\Collector::populatedNamespaces[]
        $reflectionProperty = new \ReflectionProperty(Statistics\Collector\Collector::class, "populatedNamespaces");
        $reflectionProperty->setAccessible(true);

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);

        $numberOfPlanets = $this->statsCollector->getStat("planets");
        $populatedNamespaces = array_flip($reflectionProperty->getValue($this->statsCollector));
        // stat is set and namespace is stored within $populatedNamespaces
        $this->assertEquals(8, $numberOfPlanets);
        $this->assertArrayHasKey("test_namespace.planets", $populatedNamespaces);

        $this->statsCollector->removeStat('planets');

        $numberOfPlanets = $this->statsCollector->getStat("planets");
        $populatedNamespaces = array_flip($reflectionProperty->getValue($this->statsCollector));
        // stat is null and namespace is not stored within $populatedNamespaces
        $this->assertEquals(null, $numberOfPlanets);
        $this->assertArrayNotHasKey("test_namespace.planets", $populatedNamespaces);
    }

    /**
     * Why are we prohibiting wildcards as arguments but then allowing removal of parent nodes...
     */
    public function testCanRemoveStatByRemovingParentNSNode()
    {
        //open up access to $Statistics\Collector\Collector::populatedNamespaces[]
        $reflectionProperty = new \ReflectionProperty(Statistics\Collector\Collector::class, "populatedNamespaces");
        $reflectionProperty->setAccessible(true);

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets.earth", ['radius' => '6371km']);
        $this->statsCollector->addStat("planets.mars", ['radius' => '3390km']);

        //pre-removal checks
        $planetSizes = $this->statsCollector->getStat("planets*", $withKeys = true);
        $currentPopulatedNamespaces = array_flip($reflectionProperty->getValue($this->statsCollector));

        $expected = [
          '.test_namespace.planets.earth' => ['radius' => '6371km'],
          '.test_namespace.planets.mars' => ['radius' => '3390km'],
        ];
        //// assert that stat is set and namespace is stored within $populatedNamespaces
        $this->assertEquals($expected, $planetSizes);
        $this->assertArrayHasKey("test_namespace.planets.earth", $currentPopulatedNamespaces);
        $this->assertArrayHasKey("test_namespace.planets.mars", $currentPopulatedNamespaces);

        // removal checks
        $this->statsCollector->removeStat('planets');

        $planetSizes = $this->statsCollector->getStat("test_namespace.planets*");
        $populatedNamespaces = array_flip($reflectionProperty->getValue($this->statsCollector));
        //// assert that stat is null and namespace is not stored within $populatedNamespaces
        $this->assertEquals(null, $planetSizes);
        $this->assertArrayNotHasKey("test_namespace.planets.earth", $populatedNamespaces);
        $this->assertArrayNotHasKey("test_namespace.planets.mars", $populatedNamespaces);
        $this->assertArrayNotHasKey("test_namespace.planets", $populatedNamespaces);
    }

    public function testCanCheckIfStatExists()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->assertFalse($this->statsCollector->exists("value"));

        $this->statsCollector->addStat("value", 1);
        $this->assertTrue($this->statsCollector->exists("value"));
    }

    public function testCanCheckIfStatExistsUsingWildcard()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->assertFalse($this->statsCollector->exists("users*"));

        $this->statsCollector->addStat("users.jack.age", 33);
        $this->statsCollector->addStat("users.joe.age", 29);
        $this->assertTrue($this->statsCollector->exists("users*"));
    }

    public function testCanClobberStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("value_to_be_overwritten", 1);
        $this->statsCollector->addStat("value_to_be_overwritten", 2, $options = ['clobber' => true]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals(2, $stats["test_namespace.value_to_be_overwritten"]);
    }

    public function testCanClobberCompoundStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("value_to_be_overwritten", [1]);
        $this->statsCollector->addStat("value_to_be_overwritten", [2], $options = ['clobber' => true]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([2], $stats["test_namespace.value_to_be_overwritten"]);
    }

    public function testCanClobberCompoundStatIndividualKeysAndPreserveOthers()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("value_to_be_overwritten", [
          'name' => 'jack',
          'age' => 33,
        ]);

        $this->statsCollector->addStat("value_to_be_overwritten", [
          'age' => 34,
        ], $options = ['clobber' => true]);

        $stats = $this->statsCollector->getAllStats();

        $expected = [
          'name' => 'jack',
          'age' => 34,
        ];

        $this->assertEquals($expected, $stats["test_namespace.value_to_be_overwritten"]);
    }

    public function testDefaultArrayAutoFlatteningNewStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", [1, [2, 3]]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1, 2, 3], $stats["test_namespace.compound_stat"]);
    }

    public function testCanDisableArrayFlattening()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", [1, [2, 3]], $options = ['flatten' => false]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1, [2, 3]], $stats["test_namespace.compound_stat"]);
    }

    public function testDefaultArrayAutoFlatteningToExistingStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", 1);
        $this->statsCollector->addStat("compound_stat", [2, [3, 4]]);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([1, 2, 3, 4], $stats["test_namespace.compound_stat"]);
    }

    public function testCanDisableArrayAutoFlatteningToExistingStatWhenClobbering()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("compound_stat", 1);

        $options = ['flatten' => false, 'clobber' => true];
        $this->statsCollector->addStat("compound_stat", [2, [3, 4]], $options);

        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals([2, [3, 4]], $stats["test_namespace.compound_stat"]);
    }

    public function testCanAddStatToNewSubNamespace()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("math.golden_ratio", 1.61803398875);
        $this->statsCollector->setNamespace("test_namespace.math");

        $currentNamespace = $this->statsCollector->getNamespace();
        $stats = $this->statsCollector->getAllStats();

        $this->assertEquals("test_namespace.math", $currentNamespace);
        $this->assertEquals(1.61803398875, $stats["test_namespace.math.golden_ratio"]);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testStartTimerRecordsTimestampValue()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $this->statsCollector->startTimer("test");
        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_FLOAT, $stats['test_namespace.timer.test']['start']);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testEndTimerRecordsTimestampValue()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $this->statsCollector->startTimer("test");
        $this->statsCollector->endTimer("test");

        $stats = $this->statsCollector->getAllStats();

        $this->assertInternalType(PHPUnit_IsType::TYPE_FLOAT, $stats['test_namespace.timer.test']['end']);
    }

    /**
     * @requires PHPUnit 6
     */
    public function testEndTimerRecordsCorrectTimestampsDiffValue()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $this->statsCollector->startTimer("test");
        $this->statsCollector->endTimer("test");

        $stats = $this->statsCollector->getAllStats();
        $start = $stats['test_namespace.timer.test']['start'];
        $end = $stats['test_namespace.timer.test']['end'];
        $diff = $end - $start;

        $this->assertInternalType(PHPUnit_IsType::TYPE_FLOAT, $stats['test_namespace.timer.test']['diff']);
        $this->assertEquals($diff, $stats['test_namespace.timer.test']['diff']);
    }

    public function testCanGetCorrectTimerDiffValue()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $this->statsCollector->startTimer("test");
        $this->statsCollector->endTimer("test");

        $stats = $this->statsCollector->getAllStats();
        $start = $stats['test_namespace.timer.test']['start'];
        $end = $stats['test_namespace.timer.test']['end'];
        $diff = $end - $start;

        $this->assertEquals($diff, $this->statsCollector->getTimerDiff("test"));
    }

    public function testCanGetCorrectTimerDiffValueWithCustomTimestampsSupplied()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $start=microtime(true);
        $end=microtime(true);

        $this->statsCollector->startTimer("test", $start);
        $this->statsCollector->endTimer("test", $end);

        $diff = $end - $start;

        $this->assertEquals($diff, $this->statsCollector->getTimerDiff("test"));
    }

    public function testCanGetIndividualStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);

        $numberOfPlanets = $this->statsCollector->getStat("planets");

        $this->assertEquals(8, $numberOfPlanets);
    }

    public function testCanGetIndividualStatWithKey()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);

        $numberOfPlanets = $this->statsCollector->getStat("planets", $withKeys = true);

        $expected = [
          'test_namespace.planets' => 8,
        ];
        $this->assertEquals($expected, $numberOfPlanets);
    }

    public function testCanGetMultipleStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->addStat("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getStats([
          "planets",
          "dwarf_planets",
        ]);

        $expected = [8, 1];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCallingGetStatMethodWithMultipleNamespacesReturnsMultipleStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->addStat("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getStat([
          "planets",
          "dwarf_planets",
        ]);

        $expected = [8, 1];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetMultipleStatsWithKeys()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->addStat("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getStats([
          "planets",
          "dwarf_planets",
        ], $withKeys = true);

        $expected = [
          '.test_namespace.planets' => 8,
          '.test_namespace.dwarf_planets' => 1,
        ];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetIndividualStatUsingAbsoluteNamespace()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);

        $numberOfPlanets = $this->statsCollector->getStat(".test_namespace.planets");

        $this->assertEquals(8, $numberOfPlanets);
    }

    public function testCanGetIndividualStatWithKeyUsingAbsoluteNamespace()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);

        $numberOfPlanets = $this->statsCollector->getStat(".test_namespace.planets", $withKeys = true);

        $expected = [
          'test_namespace.planets' => 8,
        ];
        $this->assertEquals($expected, $numberOfPlanets);
    }

    public function testCanGetMultipleStatsUsingAbsoluteNamespace()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->addStat("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getStats([
          ".test_namespace.planets",
          ".test_namespace.dwarf_planets",
        ]);

        $expected = [8, 1];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetMultipleStatsWithKeysUsingAbsoluteNamespace()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->addStat("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getStats([
          ".test_namespace.planets",
          ".test_namespace.dwarf_planets",
        ], $withKeys = true);

        $expected = [
          '.test_namespace.planets' => 8,
          '.test_namespace.dwarf_planets' => 1,
        ];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetIndividualStatUsingWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path");
        $this->statsCollector->addStat("pi", 3.14159265359);

        $piStat = $this->statsCollector->getStat("this.*.pi");

        $this->assertEquals(3.14159265359, $piStat);
    }

    public function testCanGetIndividualStatUsingAbsolutePathWithWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path");
        $this->statsCollector->addStat("pi", 3.14159265359);

        $piStat = $this->statsCollector->getStat(".this.is.*.pi");

        $this->assertEquals(3.14159265359, $piStat);
    }

    public function testCanGetIndividualStatWithKeyUsingWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path");
        $this->statsCollector->addStat("pi", 3.14159265359);

        $piStat = $this->statsCollector->getStat("this.*.pi", $withKeys = true);

        $expected = [
          '.this.is.a.really.long.namespace.path.pi' => 3.14159265359,
        ];

        $this->assertEquals($expected, $piStat);
    }

    public function testCanGetMultipleStatsUsingWildcardOperatorTargetingLeafNodes()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->addStat("pi", 3.14159265359);
        $this->statsCollector->addStat("golden_ratio", 1.61803398875);

        $wildcardLeafNodes = $this->statsCollector->getStats([
          "this.*.pi",
          "this.*.golden_ratio",
        ]);

        $expected = [
          3.14159265359,
          1.61803398875,
        ];

        $this->assertEquals($expected, $wildcardLeafNodes);
    }

    public function testCanGetMultipleStatsUsingWildcardOperatorTargetingCommonParentNode()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->addStat("pi", 3.14159265359);
        $this->statsCollector->addStat("golden_ratio", 1.61803398875);

        $wildcardConstantCommonParentChildNodes = $this->statsCollector->getStat("this.*.math.constants.*");

        $expected = [
          1.61803398875,
          3.14159265359,
        ];

        $this->assertEquals($expected, $wildcardConstantCommonParentChildNodes);
    }

    public function testCanGetMultipleStatsWithKeysUsingWildcardOperatorTargetingLeafNodes()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->addStat("pi", 3.14159265359);
        $this->statsCollector->addStat("golden_ratio", 1.61803398875);

        $wildcardLeafNodes = $this->statsCollector->getStats([
          "this.*.pi",
          "this.*.golden_ratio",
        ], $withKeys = true);

        $expected = [
          '.this.is.a.really.long.namespace.path.with.math.constants.pi' => 3.14159265359,
          '.this.is.a.really.long.namespace.path.with.math.constants.golden_ratio' => 1.61803398875,
        ];

        $this->assertEquals($expected, $wildcardLeafNodes);
    }

    public function testCanGetMultipleStatsWithKeysUsingWildcardOperatorTargetingCommonParentNode()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->addStat("pi", 3.14159265359);
        $this->statsCollector->addStat("golden_ratio", 1.61803398875);

        $wildcardConstantCommonParentChildNodes = $this->statsCollector->getStats([
          "this.*.math.constants.*",
        ], $withKeys = true);

        $expected = [
          '.this.is.a.really.long.namespace.path.with.math.constants.golden_ratio' => 1.61803398875,
          '.this.is.a.really.long.namespace.path.with.math.constants.pi' => 3.14159265359,
        ];

        $this->assertEquals($expected, $wildcardConstantCommonParentChildNodes);
    }

    public function testCanSetDefaultResultIfStatDoesNotExist()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $nonExistentStat = $this->statsCollector->getStat("i_dont_exist", $withKeys = false, $default = false);

        $this->assertFalse($nonExistentStat);
    }

    public function testCanSetDefaultResultIfStatWithKeysDoesNotExist()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $nonExistentStat = $this->statsCollector->getStat("i_dont_exist", $withKeys = true, $default = false);

        $expected = [
          "i_dont_exist" => false,
        ];

        $this->assertEquals($expected, $nonExistentStat);
    }

    public function testCanSetDefaultResultForMultipleResultsIfStatsDoNotExist()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $nonExistentStats = $this->statsCollector->getStats([
          "i_dont_exist",
          "i_dont_exist_either",
        ], $withKeys = false, $default = false);

        $expected = [
          false,
          false,
        ];

        $this->assertEquals($expected, $nonExistentStats);
    }

    public function testCanSetDefaultResultForMultipleResultsWithKeysIfStatsDoNotExist()
    {
        $this->statsCollector->setNamespace("test_namespace");

        $nonExistentStats = $this->statsCollector->getStats([
          "i_dont_exist",
          "i_dont_exist_either",
        ], $withKeys = true, $default = false);

        $expected = [
          '.test_namespace.i_dont_exist' => false,
          '.test_namespace.i_dont_exist_either' => false,
        ];

        $this->assertEquals($expected, $nonExistentStats);
    }

    public function testCanIncrementStatWithDefaultIncrement()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counter", 1);
        $this->statsCollector->incrementStat("counter");

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(2, $counter);
    }

    public function testCanIncrementStatWithCustomIncrementer()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counter", 1);
        $this->statsCollector->incrementStat("counter", $increment = 2);

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(3, $counter);
    }

    public function testCanIncrementCompoundStatValuesWithDefaultIncrement()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->incrementCompoundStat("counters");

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([2, 3, 4], $counters);
    }

    public function testCanIncrementCompoundStatValuesWithCustomIncrement()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->incrementCompoundStat("counters", 5);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([6, 7, 8], $counters);
    }

    public function testCanIncrementCompoundStatValuesWithArrayOfIncrements()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->incrementCompoundStat("counters", [9, 8, 7]);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([10, 10, 10], $counters);
    }

    public function testCanIncrementCompoundStatValuesWithAssociativeArrayOfIncrements()
    {
        $compoundStatValues = [
          'one' => 1,
          'two' => 2,
          'three' => 3,
        ];

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", $compoundStatValues);

        $compoundStatIncrements = [
          'one' => 5,
          'two' => 5,
          'three' => 5,
        ];
        $this->statsCollector->incrementCompoundStat("counters", $compoundStatIncrements);

        $expected = [
          'one' => 6,
          'two' => 7,
          'three' => 8,
        ];
        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals($expected, $counters);
    }

    public function testIncrementingEmptyStatCreatesNewStatAndIncrementsValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->incrementStat("counter");

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(1, $counter);
    }

    public function testIncrementingEmptyCompoundStatCreatesNewCompoundStatAndIncrementsValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->incrementCompoundStat("counters");

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([1], $counters);
    }

    public function testIncrementingEmptyCompoundStatWithArrayOfIncrementsCreatesNewCompoundStatAndIncrementsValues()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->incrementCompoundStat("counters", [1, 2, 1]);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([1, 2, 1], $counters);
    }

    public function testIncrementingCompoundStatWithUnsetArrayKeyCreatesNewCompoundStatKeyAndIncrementsValue()
    {
        $compoundStatValues = [
          'one' => 1,
          'two' => 2,
          'three' => 3,
        ];

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", $compoundStatValues);

        $compoundStatIncrements = [
          'one' => 5,
          'two' => 5,
          'three' => 5,
          'four' => 5, // I don't exist
        ];

        $this->statsCollector->incrementCompoundStat("counters", $compoundStatIncrements);

        $expected = [
          'one' => 6,
          'two' => 7,
          'three' => 8,
          'four' => 5,
        ];
        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals($expected, $counters);
    }

    /**
     * @requires PHPUnit 5
     */
    public function testIncrementStatWhichIsNotIncrementableThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempted to increment a value which cannot be incremented!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("text", "dummy text");
        $this->statsCollector->incrementStat("text");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testIncrementCompoundStatWhichIsNotIncrementableThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to increment a compound value which cannot be incremented! (counters[2]=\"three\":string)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, "three"]);
        $this->statsCollector->incrementCompoundStat("counters");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testCallingIncrementCompoundStatOnNonCompoundStatThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("The stat you are trying to increment is not a compound stat, instead use incrementStat(). counters=1(integer)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", 1);
        $this->statsCollector->incrementCompoundStat("counters");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testIncrementCompoundStatWithANonNumberThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to increment a compound stat with a value which is not a float or integer!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->incrementCompoundStat("counters", "five");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testIncrementCompoundStatWithANonNumberInArrayOfIncrementValuesThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to increment a compound stat with a value which is not a float or integer!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->incrementCompoundStat("counters", [5, 5, "five"]);
    }

    public function testCanDecrementStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counter", 10);
        $this->statsCollector->decrementStat("counter");

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(9, $counter);
    }

    public function testCanDecrementStatWithCustomDecrementer()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counter", 10);
        $this->statsCollector->decrementStat("counter", $decrement = 5);

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(5, $counter);
    }

    public function testCanDecrementCompoundStatValuesWithDefaultDecrement()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->decrementCompoundStat("counters");

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([0, 1, 2], $counters);
    }

    public function testCanDecrementCompoundStatValuesWithCustomDecrement()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [10, 15, 20]);
        $this->statsCollector->decrementCompoundStat("counters", 5);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([5, 10, 15], $counters);
    }

    public function testCanDecrementCompoundStatValuesWithArrayOfDecrements()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [10, 10, 10]);
        $this->statsCollector->decrementCompoundStat("counters", [5, 5, 5]);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([5, 5, 5], $counters);
    }

    public function testCanDecrementCompoundStatValuesWithAssociativeArrayOfDecrements()
    {
        $compoundStatValues = [
          'one' => 10,
          'two' => 15,
          'three' => 20,
        ];

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", $compoundStatValues);

        $compoundStatDecrements = [
          'one' => 5,
          'two' => 5,
          'three' => 5,
        ];
        $this->statsCollector->decrementCompoundStat("counters", $compoundStatDecrements);

        $expected = [
          'one' => 5,
          'two' => 10,
          'three' => 15,
        ];
        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals($expected, $counters);
    }

    public function testDecrementingEmptyStatCreatesNewStatAndDecrementsIt()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->decrementStat("counter");

        $counter = $this->statsCollector->getStat("counter");

        $this->assertEquals(-1, $counter);
    }

    public function testDecrementingEmptyCompoundStatCreatesNewCompoundStatAndDecrementsValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->decrementCompoundStat("counters");

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([-1], $counters);
    }

    public function testDecrementingEmptyCompoundStatWithArrayOfDecrementsCreatesNewCompoundStatAndDecrementsValues()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->decrementCompoundStat("counters", [1, 2, 1]);

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals([-1, -2, -1], $counters);
    }

    public function testDecrementingCompoundStatWithUnsetArrayKeyCreatesNewCompoundStatKeyAndDecrementsValue()
    {
        $compoundStatValues = [
          'one' => 10,
          'two' => 20,
          'three' => 30,
        ];

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", $compoundStatValues);

        $compoundStatDecrements = [
          'one' => 5,
          'two' => 5,
          'three' => 5,
          'four' => 5, // I don't exist
        ];

        $this->statsCollector->decrementCompoundStat("counters", $compoundStatDecrements);

        $expected = [
          'one' => 5,
          'two' => 15,
          'three' => 25,
          'four' => -5,
        ];

        $counters = $this->statsCollector->getStat("counters");

        $this->assertEquals($expected, $counters);
    }

    /**
     * @requires PHPUnit 5
     */
    public function testDecrementStatWhichIsNotDecrementableThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempted to decrement a value which cannot be decremented!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("text", "dummy text");
        $this->statsCollector->decrementStat("text");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testDecrementCompoundStatWhichIsNotDecrementableThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to decrement a compound value which cannot be decremented! (counters[2]=\"three\":string)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, "three"]);
        $this->statsCollector->decrementCompoundStat("counters");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testCallingDecrementCompoundStatOnNonCompoundStatThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("The stat you are trying to decrement is not a compound stat, instead use decrementStat(). counters=1(integer)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", 1);
        $this->statsCollector->decrementCompoundStat("counters");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testDecrementCompoundStatWithANonNumberThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to decrement a compound stat with a value which is not a float or integer!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->decrementCompoundStat("counters", "five");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testDecrementCompoundStatWithANonNumberInArrayOfDecrementValuesThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to decrement a compound stat with a value which is not a float or integer!");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counters", [1, 2, 3]);
        $this->statsCollector->decrementCompoundStat("counters", [5, 5, "five"]);
    }

    /**
     * @requires PHPUnit 5
     */
    public function testRemovingStatsWithWildcardThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Wildcard usage forbidden when removing stats (to protect you from yourself!)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("planets", 8);
        $this->statsCollector->removeStat('test_namespace.*');
    }

    /**
     * @requires PHPUnit 5
     */
    public function testRemovingNonExistentStatsThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("Attempting to remove a statistic that does not exist: test_namespace.i_dont_exist");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->removeStat('test_namespace.i_dont_exist');
    }

    public function testCanGetCountOfIndividualStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("heights", 181.5);

        $numberOfHeights = $this->statsCollector->getStatCount("heights");

        $this->assertEquals(1, $numberOfHeights);
    }

    public function testCanGetCountOfIndividualCompoundStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("heights", [181, 222, 194, 143, 190]);

        $numberOfHeights = $this->statsCollector->getStatCount("heights");

        $this->assertEquals(5, $numberOfHeights);
    }

    public function testCanGetCountOfMultipleCompoundStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("heights", [181, 222, 194, 143, 190]);
        $this->statsCollector->addStat("weights", [200, 211, 173, 130, 187]);

        $combinedNumberOfHeightsAndWeights = $this->statsCollector->getStatsCount([
          'heights',
          'weights',
        ]);

        $this->assertEquals(10, $combinedNumberOfHeightsAndWeights);
    }

    public function testCanGetCountOfMultipleCompoundStatsUsingWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("measurements.heights", [181, 222, 194, 143, 190]);
        $this->statsCollector->addStat("measurements.weights", [200, 211, 173, 130, 187]);

        $combinedNumberOfHeightsAndWeights = $this->statsCollector->getStatCount("measurements.*");

        $this->assertEquals(10, $combinedNumberOfHeightsAndWeights);
    }

    public function testCanGetAverageValuesOfIndividualStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("heights", 181.5);

        $averageHeight = $this->statsCollector->getStatAverage("heights");

        $this->assertEquals(181.5, $averageHeight);
    }

    public function testCanGetAverageValuesOfIndividualCompoundStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $heights = [181, 222, 194, 143, 190];
        $this->statsCollector->addStat("heights", $heights);

        $averageHeight = $this->statsCollector->getStatAverage("heights");
        $expectedAverage = array_sum($heights) / count($heights); // 186

        $this->assertEquals($expectedAverage, $averageHeight);
    }

    public function testCanGetAverageValuesOfMultipleStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $gondorHeights = [181, 222, 194, 143, 190];
        $shireHeights = [96, 110, 85, 120, 111];
        $mordorHeight = 140;
        $this->statsCollector->addStat("gondor_heights", $gondorHeights);
        $this->statsCollector->addStat("shire_heights", $shireHeights);
        $this->statsCollector->addStat("mordor_height", $mordorHeight);

        $averageHeightAcrossMiddleEarth = $this->statsCollector->getStatsAverage([
          'gondor_heights',
          'shire_heights',
          'mordor_height',
        ]);

        $combinedHeights = array_merge($gondorHeights, $shireHeights, [$mordorHeight]);
        $expectedCombinedHeightsAverage = array_sum($combinedHeights) / count($combinedHeights); // 144.72727272727272

        $this->assertEquals($expectedCombinedHeightsAverage, $averageHeightAcrossMiddleEarth);
    }

    public function testCanGetAverageValuesOfMultipleStatsUsingWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $gondorHeights = [181, 222, 194, 143, 190];
        $shireHeights = [96, 110, 85, 120, 111];
        $this->statsCollector->addStat("middle_earth.gondor_heights", $gondorHeights);
        $this->statsCollector->addStat("middle_earth.shire_heights", $shireHeights);

        $averageHeightAcrossGondorAndTheShire = $this->statsCollector->getStatAverage("middle_earth.*");

        $combinedHeights = array_merge($gondorHeights, $shireHeights);
        $expectedCombinedHeightsAverage = array_sum($combinedHeights) / count($combinedHeights); // 145.2

        $this->assertEquals($expectedCombinedHeightsAverage, $averageHeightAcrossGondorAndTheShire);
    }

    /**
     * @requires PHPUnit 5
     */
    public function testTryingToAverageANonNumberThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("An error occurred with \"heights\" - Unable to return average for supplied arguments (are the values numeric?)");

        $this->statsCollector->setNamespace("test_namespace");
        $heights = [181, 222, 194, 143, 190, "one hundred and fifty"];
        $this->statsCollector->addStat("heights", $heights);

        $this->statsCollector->getStatAverage("heights");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testTryingToAverageMultipleNonNumbersThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("An error occurred with \"heights.height1\",\"heights.height2\" - Unable to return average for supplied arguments (are the values numeric?)");

        $this->statsCollector->setNamespace("test_namespace");
        $height1 = ["one hundred and fifty"];
        $height2 = ["one hundred and sixty"];
        $this->statsCollector->addStat("heights.height1", $height1);
        $this->statsCollector->addStat("heights.height2", $height2);

        $this->statsCollector->getStatsAverage(['heights.height1', 'heights.height2']);
    }

    public function testCanGetSumOfSingleValue()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("double", 0.5);

        $double = $this->statsCollector->getStatSum("double");

        $this->assertEquals(0.5, $double);
    }

    public function testCanGetSumOfIndividualStat()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->addStat("counter", [1, 2, 3, 4, 5]);

        $counterSum = $this->statsCollector->getStatSum("counter");

        $this->assertEquals(15, $counterSum);
    }

    public function testCanGetSumOfMultipleStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("noahs.ark.passengers");
        $this->statsCollector->addStat("humans", 2);
        $this->statsCollector->addStat("aliens", 0);
        $this->statsCollector->addStat("animals", 99);

        $numberOfPassengers = $this->statsCollector->getStatsSum([
          "humans",
          "aliens",
          "animals",
        ]);

        $this->assertEquals(101, $numberOfPassengers);
    }

    public function testCanGetSumOfMultipleStatsUsingWildcardOperator()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("noahs.ark.passengers");
        $this->statsCollector->addStat("humans", 2);
        $this->statsCollector->addStat("aliens", 0);
        $this->statsCollector->addStat("animals", 99);

        $numberOfPassengers = $this->statsCollector->getStatSum("noahs.ark.passengers.*");

        $this->assertEquals(101, $numberOfPassengers);
    }

    /**
     * @requires PHPUnit 5
     */
    public function testTryingToSumANonNumberThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("An error occurred with \"noahs.ark.passengers.*\" - Unable to return sum for supplied arguments (are the values numeric?)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("noahs.ark.passengers");
        $this->statsCollector->addStat("humans", "two");
        $this->statsCollector->addStat("aliens", 0);
        $this->statsCollector->addStat("animals", 99);

        $this->statsCollector->getStatSum("noahs.ark.passengers.*");
    }

    /**
     * @requires PHPUnit 5
     */
    public function testTryingToSumMultipleNonNumbersThrowsException()
    {
        $this->expectException(Statistics\Exception\StatisticsCollectorException::class);
        $this->expectExceptionMessage("An error occurred with \"passengers.humans\",\"passengers.aliens\" - Unable to return sum for supplied arguments (are the values numeric?)");

        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("passengers");
        $this->statsCollector->addStat("humans", "two");
        $this->statsCollector->addStat("aliens", "zero");

        $this->statsCollector->getStatsSum(["passengers.humans", "passengers.aliens"]);
    }

    public function testCanGetAllAddedStats()
    {
        $this->statsCollector->setNamespace("test_namespace");
        $this->statsCollector->setNamespace("noahs.ark.passengers");
        $this->statsCollector->addStat("humans", 2);
        $this->statsCollector->addStat("aliens", 0);
        $this->statsCollector->addStat("animals", 99);

        $allStats = $this->statsCollector->getAllStats();

        //stats are returned in alphabetical order
        $expectStats = [
          'noahs.ark.passengers.aliens' => 0,
          'noahs.ark.passengers.animals' => 99,
          'noahs.ark.passengers.humans' => 2,
        ];

        $this->assertEquals($expectStats, $allStats);
    }

    /**
     * @covers \Statistics\Collector\Traits\SingletonInheritance::tearDown()
     */
    public function tearDown()
    {
        Statistics\Collector\Collector::tearDown();
        parent::tearDown();
    }

}
