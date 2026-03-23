<?php

/**
 * @covers \Statistics\Collector\Traits\CollectorShorthand
 * @covers \Statistics\Collector\Traits\SingletonInheritance
 * @covers \Statistics\Collector\Collector<extended>
 */
class CollectorShorthandTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Statistics\Collector\Collector
     */
    protected $statsCollector;

    public function setUp(): void
    {
        $this->statsCollector = Statistics\Collector\Collector::getInstance();
        parent::setUp();
    }

    public function testCollectorImplementsCollectorShorthandInterface()
    {
        $this->assertInstanceOf(Statistics\Collector\iCollectorShorthand::class, $this->statsCollector);
    }

    public function testCanChangeRootNamespace()
    {
        $this->statsCollector->ns("phpunit");

        $currentNamespace = $this->statsCollector->ns();

        $this->assertEquals("phpunit", $currentNamespace);
    }

    public function testCanAddStat()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("number_of_planets", 8);

        $stats = $this->statsCollector->all();

        $this->assertEquals(8, $stats["test_namespace.number_of_planets"]);
    }

    public function testCanAddIntegerAsStat()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("days_per_year", 365);

        $stats = $this->statsCollector->all();

        $this->assertIsInt($stats["test_namespace.days_per_year"]);
    }

    public function testCanAddFloatAsStat()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("pi", 3.14159265359);

        $stats = $this->statsCollector->all();

        $this->assertIsFloat($stats["test_namespace.pi"]);
    }

    public function testCanAddArrayAsStat()
    {
        $this->statsCollector->ns("test_namespace");

        $fibonacciSequence = [0, 1, 1, 2, 3, 5, 8, 13, 21, 34];
        $this->statsCollector->add("fibonacci_sequence", $fibonacciSequence);

        $stats = $this->statsCollector->all();

        $this->assertIsArray($stats["test_namespace.fibonacci_sequence"]);
    }

    public function testCanAddAssociativeArrayAsStat()
    {
        $mathConstants = [
          "pi" => 3.14159265359,
          'golden_ratio' => 1.61803398875,
        ];

        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("math_constants", $mathConstants);
        $stats = $this->statsCollector->all();

        $expected = [
          "pi" => 3.14159265359,
          'golden_ratio' => 1.61803398875,
        ];

        $this->assertIsArray($stats["test_namespace.math_constants"]);
        $this->assertEquals($expected, $stats["test_namespace.math_constants"]);
    }

    public function testCanClobberStat()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("value_to_be_overwritten", 1);
        $this->statsCollector->clobber("value_to_be_overwritten", 2);

        $stats = $this->statsCollector->all();

        $this->assertEquals(2, $stats["test_namespace.value_to_be_overwritten"]);
    }

    public function testCanAddStatToNewSubNamespace()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("math.golden_ratio", 1.61803398875);
        $this->statsCollector->ns("test_namespace.math");

        $currentNamespace = $this->statsCollector->ns();
        $stats = $this->statsCollector->all();

        $this->assertEquals("test_namespace.math", $currentNamespace);
        $this->assertEquals(1.61803398875, $stats["test_namespace.math.golden_ratio"]);
    }

    public function testCanGetIndividualStat()
    {
        $this->statsCollector->add("planets", 8);

        $numberOfPlanets = $this->statsCollector->get("planets");

        $this->assertEquals(8, $numberOfPlanets);
    }

    public function testCanGetIndividualStatWithKey()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);

        $numberOfPlanets = $this->statsCollector->getWithKey("planets");

        $expected = [
          'test_namespace.planets' => 8,
        ];
        $this->assertEquals($expected, $numberOfPlanets);
    }

    public function testCanGetMultipleStats()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);
        $this->statsCollector->add("dwarf_planets", 1);

        $planetStats = $this->statsCollector->get([
          "planets",
          "dwarf_planets",
        ]);

        $expected = [8, 1];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetMultipleStatsWithKeys()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);
        $this->statsCollector->add("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getWithKey([
          "planets",
          "dwarf_planets",
        ]);

        $expected = [
          'test_namespace.planets' => 8,
          'test_namespace.dwarf_planets' => 1,
        ];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetIndividualStatUsingAbsoluteNamespace()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);

        $numberOfPlanets = $this->statsCollector->get(".test_namespace.planets");

        $this->assertEquals(8, $numberOfPlanets);
    }

    public function testCanGetIndividualStatWithKeyUsingAbsoluteNamespace()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);

        $numberOfPlanets = $this->statsCollector->getWithKey(".test_namespace.planets");

        $expected = [
          'test_namespace.planets' => 8,
        ];
        $this->assertEquals($expected, $numberOfPlanets);
    }

    public function testCanGetMultipleStatsUsingAbsoluteNamespace()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);
        $this->statsCollector->add("dwarf_planets", 1);

        $planetStats = $this->statsCollector->get([
          ".test_namespace.planets",
          ".test_namespace.dwarf_planets",
        ]);

        $expected = [8, 1];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetMultipleStatsWithKeysUsingAbsoluteNamespace()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->add("planets", 8);
        $this->statsCollector->add("dwarf_planets", 1);

        $planetStats = $this->statsCollector->getWithKey([
          ".test_namespace.planets",
          ".test_namespace.dwarf_planets",
        ]);

        $expected = [
          'test_namespace.planets' => 8,
          'test_namespace.dwarf_planets' => 1,
        ];

        $this->assertEquals($expected, $planetStats);
    }

    public function testCanGetIndividualStatUsingWildcardOperator()
    {
        $this->statsCollector->ns("test_namespace");
        $this->statsCollector->ns("this.is.a.really.long.namespace.path");
        $this->statsCollector->add("pi", 3.14159265359);

        $piStat = $this->statsCollector->get("this.*.pi");

        $this->assertEquals(3.14159265359, $piStat);
    }

    public function testCanGetIndividualStatWithKeyUsingWildcardOperator()
    {
        $this->statsCollector->ns("this.is.a.really.long.namespace.path");
        $this->statsCollector->add("pi", 3.14159265359);

        $piStat = $this->statsCollector->getWithKey("this.*.pi");

        $expected = [
          'this.is.a.really.long.namespace.path.pi' => 3.14159265359,
        ];

        $this->assertEquals($expected, $piStat);
    }

    public function testCanGetMultipleStatsUsingWildcardOperatorTargetingLeafNodes()
    {
        $this->statsCollector->ns("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->add("pi", 3.14159265359);
        $this->statsCollector->add("golden_ratio", 1.61803398875);

        $wildcardLeafNodes = $this->statsCollector->get([
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
        $this->statsCollector->ns("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->add("pi", 3.14159265359);
        $this->statsCollector->add("golden_ratio", 1.61803398875);

        $wildcardConstantCommonParentChildNodes = $this->statsCollector->get([
          "this.*.math.constants.*",
        ]);

        $expected = [
          1.61803398875,
          3.14159265359,
        ];

        $this->assertEquals($expected, $wildcardConstantCommonParentChildNodes);
    }

    public function testCanGetMultipleStatsWithKeysUsingWildcardOperatorTargetingLeafNodes()
    {
        $this->statsCollector->ns("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->add("pi", 3.14159265359);
        $this->statsCollector->add("golden_ratio", 1.61803398875);

        $wildcardLeafNodes = $this->statsCollector->getWithKey([
          "this.*.pi",
          "this.*.golden_ratio",
        ]);

        $expected = [
          'this.is.a.really.long.namespace.path.with.math.constants.pi' => 3.14159265359,
          'this.is.a.really.long.namespace.path.with.math.constants.golden_ratio' => 1.61803398875,
        ];

        $this->assertEquals($expected, $wildcardLeafNodes);
    }

    public function testCanGetMultipleStatsWithKeysUsingWildcardOperatorTargetingCommonParentNode()
    {
        $this->statsCollector->ns("this.is.a.really.long.namespace.path.with.math.constants");
        $this->statsCollector->add("pi", 3.14159265359);
        $this->statsCollector->add("golden_ratio", 1.61803398875);

        $wildcardConstantCommonParentChildNodes = $this->statsCollector->getWithKey("this.*.math.constants.*");

        $expected = [
          'this.is.a.really.long.namespace.path.with.math.constants.golden_ratio' => 1.61803398875,
          'this.is.a.really.long.namespace.path.with.math.constants.pi' => 3.14159265359,
        ];

        $this->assertEquals($expected, $wildcardConstantCommonParentChildNodes);
    }

    public function testCanSetDefaultResultIfStatDoesNotExist()
    {
        $nonExistentStat = $this->statsCollector->get("i_dont_exist", $withKeys = false, $default = false);

        $this->assertFalse($nonExistentStat);
    }

    public function testCanSetDefaultResultForMultipleResultsIfStatsDoesNotExist()
    {
        $nonExistentStats = $this->statsCollector->get([
          "i_dont_exist",
          "i_dont_exist_either",
        ], $withKeys = false, $default = false);

        $expected = [
          false,
          false,
        ];

        $this->assertEquals($expected, $nonExistentStats);
    }

    public function testCanIncrementStat()
    {
        $this->statsCollector->add("counter", 1);
        $this->statsCollector->inc("counter");

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals(2, $counter);
    }

    public function testCanIncrementStatWithCustomIncrementer()
    {
        $this->statsCollector->add("counter", 1);
        $this->statsCollector->inc("counter", $increment = 2);

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals(3, $counter);
    }

    public function testCanIncrementCompoundStat()
    {
        $this->statsCollector->add("counter", [1, 2, 3]);
        $this->statsCollector->incCpd("counter", 5);

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals([6, 7, 8], $counter);
    }

    public function testCanDecrementStat()
    {
        $this->statsCollector->add("counter", 10);
        $this->statsCollector->dec("counter");

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals(9, $counter);
    }

    public function testCanDecrementStatWithCustomDecrementer()
    {
        $this->statsCollector->add("counter", 10);
        $this->statsCollector->dec("counter", $decrement = 5);

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals(5, $counter);
    }

    public function testCanDecrementCompoundStat()
    {
        $this->statsCollector->add("counter", [6, 7, 8]);
        $this->statsCollector->decCpd("counter", 5);

        $counter = $this->statsCollector->get("counter");

        $this->assertEquals([1, 2, 3], $counter);
    }



    public function testStartTimerRecordsTimestampValue()
    {
        $this->statsCollector->ns("test_namespace");

        $this->statsCollector->start("test");
        $stats = $this->statsCollector->all();

        $this->assertIsFloat($stats['test_namespace.timer.test']['start']);
    }

    public function testEndTimerRecordsTimestampValue()
    {
        $this->statsCollector->ns("test_namespace");

        $this->statsCollector->start("test");
        $this->statsCollector->end("test");

        $stats = $this->statsCollector->all();

        $this->assertIsFloat($stats['test_namespace.timer.test']['end']);
    }

    public function testEndTimerRecordsCorrectTimestampsDiffValue()
    {
        $this->statsCollector->ns("test_namespace");

        $this->statsCollector->start("test");
        $this->statsCollector->end("test");

        $stats = $this->statsCollector->all();
        $start = $stats['test_namespace.timer.test']['start'];
        $end = $stats['test_namespace.timer.test']['end'];
        $diff = $end - $start;

        $this->assertIsFloat($stats['test_namespace.timer.test']['diff']);
        $this->assertEquals($diff, $stats['test_namespace.timer.test']['diff']);
    }

    public function testCanGetCorrectTimerDiffValue()
    {
        $this->statsCollector->ns("test_namespace");

        $this->statsCollector->start("test");
        $this->statsCollector->end("test");

        $stats = $this->statsCollector->all();
        $start = $stats['test_namespace.timer.test']['start'];
        $end = $stats['test_namespace.timer.test']['end'];
        $diff = $end - $start;

        $this->assertEquals($diff, $this->statsCollector->diff("test"));
    }

    public function testCanRemoveStat()
    {
        $this->statsCollector->add("planets", 8);

        $numberOfPlanets = $this->statsCollector->get("planets");
        $this->assertEquals(8, $numberOfPlanets);

        $this->statsCollector->del('planets');
        $numberOfPlanets = $this->statsCollector->get("planets");
        $this->assertEquals(null, $numberOfPlanets);
    }

    public function testCanGetCountOfIndividualStat()
    {
        $this->statsCollector->add("heights", [181, 222, 194, 143, 190]);

        $numberOfHeights = $this->statsCollector->count("heights");

        $this->assertEquals(5, $numberOfHeights);
    }

    public function testCanGetCountOfMultipleStats()
    {
        $this->statsCollector->add("heights", [181, 222, 194, 143, 190]);
        $this->statsCollector->add("weights", [200, 211, 173, 130, 187]);

        $combinedNumberOfHeightsAndWeights = $this->statsCollector->count([
          'heights',
          'weights',
        ]);

        $this->assertEquals(10, $combinedNumberOfHeightsAndWeights);
    }

    public function testCanGetCountOfMultipleStatsUsingWildcardOperator()
    {
        $this->statsCollector->add("measurements.heights", [181, 222, 194, 143, 190]);
        $this->statsCollector->add("measurements.weights", [200, 211, 173, 130, 187]);

        $combinedNumberOfHeightsAndWeights = $this->statsCollector->count("measurements.*");

        $this->assertEquals(10, $combinedNumberOfHeightsAndWeights);
    }

    public function testCanGetAverageValuesOfIndividualStat()
    {
        $heights = [181, 222, 194, 143, 190];
        $this->statsCollector->add("heights", $heights);

        $averageHeight = $this->statsCollector->avg("heights");
        $expectedAverage = array_sum($heights) / count($heights); // 186

        $this->assertEquals($expectedAverage, $averageHeight);
    }

    public function testCanGetAverageValuesOfMultipleStats()
    {
        $gondorHeights = [181, 222, 194, 143, 190];
        $shireHeights = [96, 110, 85, 120, 111];
        $this->statsCollector->add("gondor_heights", $gondorHeights);
        $this->statsCollector->add("shire_heights", $shireHeights);

        $averageHeightAcrossGondorAndTheShire = $this->statsCollector->avg([
          'gondor_heights',
          'shire_heights',
        ]);

        $combinedHeights = array_merge($gondorHeights, $shireHeights);
        $expectedCombinedHeightsAverage = array_sum($combinedHeights) / count($combinedHeights); // 145.2

        $this->assertEquals($expectedCombinedHeightsAverage, $averageHeightAcrossGondorAndTheShire);
    }

    public function testCanGetAverageValuesOfMultipleStatsUsingWildcardOperator()
    {
        $gondorHeights = [181, 222, 194, 143, 190];
        $shireHeights = [96, 110, 85, 120, 111];
        $this->statsCollector->add("middle_earth.gondor_heights", $gondorHeights);
        $this->statsCollector->add("middle_earth.shire_heights", $shireHeights);

        $averageHeightAcrossGondorAndTheShire = $this->statsCollector->avg("middle_earth.*");

        $combinedHeights = array_merge($gondorHeights, $shireHeights);
        $expectedCombinedHeightsAverage = array_sum($combinedHeights) / count($combinedHeights); // 145.2

        $this->assertEquals($expectedCombinedHeightsAverage, $averageHeightAcrossGondorAndTheShire);
    }

    public function testCanGetSumOfIndividualStat()
    {
        $this->statsCollector->add("counter", [1, 2, 3, 4, 5]);

        $counterSum = $this->statsCollector->sum("counter");

        $this->assertEquals(15, $counterSum);
    }

    public function testCanGetSumOfMultipleStats()
    {
        $this->statsCollector->ns("noahs.ark.passengers");
        $this->statsCollector->add("humans", 2);
        $this->statsCollector->add("aliens", 0);
        $this->statsCollector->add("animals", 99);

        $numberOfPassengers = $this->statsCollector->sum([
          "humans",
          "aliens",
          "animals",
        ]);

        $this->assertEquals(101, $numberOfPassengers);
    }

    public function testCanGetSumOfMultipleStatsUsingWildcardOperator()
    {
        $this->statsCollector->ns("noahs.ark.passengers");
        $this->statsCollector->add("humans", 2);
        $this->statsCollector->add("aliens", 0);
        $this->statsCollector->add("animals", 99);

        $numberOfPassengers = $this->statsCollector->sum("noahs.ark.passengers.*");

        $this->assertEquals(101, $numberOfPassengers);
    }

    public function testCanGetAllAddedStats()
    {
        $this->statsCollector->ns("noahs.ark.passengers");
        $this->statsCollector->add("humans", 2);
        $this->statsCollector->add("aliens", 0);
        $this->statsCollector->add("animals", 99);

        $allStats = $this->statsCollector->all();

        //stats are returned in alphabetical order
        $expectStats = [
          'noahs.ark.passengers.aliens' => 0,
          'noahs.ark.passengers.animals' => 99,
          'noahs.ark.passengers.humans' => 2,
        ];

        $this->assertEquals($expectStats, $allStats);
    }

    public function tearDown(): void
    {
        Statistics\Collector\Collector::tearDown();
        parent::tearDown();
    }

}
