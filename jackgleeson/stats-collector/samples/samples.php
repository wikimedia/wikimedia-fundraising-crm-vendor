<?php
require __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'CiviCRMCollector.php';

/**
 * Get an instance of the Collector
 */
$statsCollector = Statistics\Collector\Collector::getInstance();

/**
 * Setting & Getting stats
 */

// basic usage (add to root namespace)
$statsCollector->addStat("users", 45); // add stat to "root" default general namespace
$users = $statsCollector->getStat("users"); // 45
$usersWithNamespaceInKey = $statsCollector->getStat("users", $withKeys = true); // Array ( [root.users] => 45 )

// define a new default namespace and add stats to it
$statsCollector->setNamespace("website")
  ->addStat("clicks", 30)
  ->addStat("banner.views", 20); // add a sub-namespace to the current namespace (in a relative fashion)

// get single stat by relative (resolves to website.clicks due to last set namespace being "website" on line 18)
$clicks = $statsCollector->getStat("clicks"); // 30 - the getStat() call is relative to your last default namespace

// get single stat by sub-namespace relative (resolves to website.banner.views)
$bannerViews = $statsCollector->getStat("banner.views"); // 20 - the getStat() call is made to website.banner.clicks

// get single stat by absolute path
$websiteClicks = $statsCollector->getStat(".website.clicks"); // 30 - prepending paths with '.' resolves to an absolute path

// get multiple stats back using absolute paths
$statsAbsolute = $statsCollector->getStats([
  '.website.clicks',
  '.website.banner.views',
]); // $statsAbsolute = Array ( [0] => 30 [1] => 20 )

// get multiple stats back using absolute paths including their full namespace as the key
$statsAbsoluteWithKeys = $statsCollector->getStats([
  '.website.clicks',
  '.website.banner.views',
],
  $withKeys = true); // $statsAbsoluteWithKeys = Array ( [website.clicks] => 30 [website.banner.views] => 20 )

// get multiple stats, one using absolute namespace and one using relative namespace
$statsRelative = $statsCollector->getStats([
  'clicks',
  '.website.banner.views',
]); // Array ( [0] => 30 [1] => 20 )

//removing a stat
$statsCollector->removeStat('clicks');

//define a long namespace, add a stat related stats and retrieve it using a wildcard operator
$statsCollector->setNamespace("this.is.a.really.long.namespace.path")
  ->addStat("age", 33);
$clicks = $statsCollector->getStat("this.*.age"); // 33

//define a namespace, add some stats and retrieve them all with wildcard paths
$statsCollector->setNamespace("transactions")
  ->addStat("mobile", 10)
  ->addStat("website", 20)
  ->addStat("tablet", 30)
  ->addStat("other", 40);

// lets get all transaction stats using the wildcard operator
$transactions = $statsCollector->getStat("transactions.*");
// $transactions = Array ( [0] => 10 [1] => 40 [2] => 30 [3] => 20 )

// lets get all transaction stats using the wildcard operator including their full namespace as the key
$transactionsWithKeys = $statsCollector->getStat("transactions.*", true);
// $transactions = Array ( [.transactions.mobile] => 10 [.transactions.other] => 40 [.transactions.tablet] => 30 [.transactions.website] => 20 )


// getStat() and getStats() will auto-deduplicate results if you accidentally include the same stat twice using wildcards
$transactionsWithUniqueStats = $statsCollector->getStats([
  "transactions.*",
  ".transactions.mobile",
]);
// only one mobile stat of '10' is present in the result $transactionsWithUniqueStats = Array ( [0] => 10 [1] => 40 [2] => 30 [3] => 20 )


/**
 * Working with basic stats, basic functions (increment/decrement)
 */

// lets increment some stats
$statsCollector->setNamespace("general.stats")
  ->addStat("days_on_the_earth", (33 * 365))// 12045 added to 'general.stats.days_on_the_earth'
  ->incrementStat("days_on_the_earth", 1); // we time travel forward 24 hours.
$daysOnEarth = $statsCollector->getStat("days_on_the_earth"); // 12046
$daysOnEarthAbsolute = $statsCollector->getStat(".general.stats.days_on_the_earth"); // same as above 12046

// lets decrement some stats
$statsCollector->setNamespace("general.other.stats")
  ->addStat("days_until_christmas", 53)// 53 as of 11/02/2017
  ->decrementStat("days_until_christmas"); // skip 24 hours
$daysUntilChristmas = $statsCollector->getStat("days_until_christmas"); // 52

/**
 * Working with basic stats, aggregate functions (sum/average)
 */

// lets add a bunch of stats and sum them
$statsCollector->setNamespace("noahs.ark.passengers")
  ->addStat("humans", 2)
  ->addStat("aliens", 0)
  ->addStat("animal.cats", 3)//adds sub-namespace 'noahs.ark.passengers.animal.cats'
  ->addStat("animal.dogs", 6)
  ->addStat("animal.chickens", 25);

// total number of passengers on noahs ark
$numberOfPassengers = $statsCollector->getStatSum("noahs.ark.passengers.*"); // 36

// lets sum up some individual stats
$statsCollector->setNamespace("visits.month")
  ->addStat("jan", 553)
  ->addStat("feb", 223)
  ->addStat("mar", 434)
  ->addStat("apr", 731)
  ->addStat("may", 136)
  ->addStat("june", 434)
  ->addStat("july", 321)
  ->addStat("aug", 353)
  ->addStat("sept", 657)
  ->addStat("oct", 575)
  ->addStat("nov", 1020)
  ->addStat("dec", 2346);


// you could use a wildcard to get the sum of visits by targeting  'visits.month.*'
$visitsForTheYearWildcard = $statsCollector->getStatSum("visits.month.*"); ////7783

// lets work out the average visits per month based on the above stats
$averageVisitsPerMonth = $statsCollector->getStatsAverage([
  'jan',
  'feb',
  'mar',
  'apr',
  'may',
  'june',
  'july',
  'aug',
  'sept',
  'oct',
  'nov',
  'dec',
]); //648.58333333333

$averageVisitsPerMonthWildcard = $statsCollector->getStatAverage("visits.month.*"); //648.58333333333


/**
 * Working with compound stats (averages/sum/count)
 *
 * Stats become "compound" when you add either an array of values to a single
 * stat or when you add a stat to an already existing namespace.
 */

// lets get the average of a compound stat
$statsCollector->setNamespace("users")
  ->addStat("age", 23)
  ->addStat("age", 12)
  ->addStat("age", 74)
  ->addStat("age", 49)
  ->addStat("age", 9);
$averageAges = $statsCollector->getStatAverage('age'); //33.4

// another way to convert to a compound stat is just to pass an array of values as the value (it will auto-flatten by default)
$statsCollector->setNamespace("users")
  ->addStat("heights", 171)
  ->addStat("heights", [181, 222, 194, 143, 123, 161, 184]);

$averageHeights = $statsCollector->getStatAverage('heights'); //172.375

// clobber/overwrite existing stat when addStating to prevent compound behaviour (e.g. updating timestamps)
$statsCollector->setNamespace("batch.jobs");
$statsCollector->addStat("last_run", strtotime('-1 day', strtotime('now')));
$statsCollector->addStat("last_run", strtotime('now'));
$runTimes = $statsCollector->getStat("last_run"); //Array ( [0] => 1510593647 [1] => 1510680047 )

$statsCollector->addStat("last_run", strtotime('-1 day', strtotime('now')));
$statsCollector->addStat("last_run", strtotime('now'), $options = ['clobber' => true]);
$runTimeSingleResult = $statsCollector->getStat("last_run"); //1510680136

// lets take three different compound stats and work out the collective sum
$statsCollector->setNamespace("website.referrals")
  ->addStat("google", 110)
  ->addStat("google", 222)
  ->addStat("google", 146)
  ->addStat("google", 125)
  ->addStat("yahoo", 510)
  ->addStat("yahoo", 148)
  ->addStat("yahoo", 2122)
  ->addStat("bing", 230)
  ->addStat("bing", 335)
  ->addStat("bing", 141);

$totalReferrals = $statsCollector->getStatsSum([
  'google',
  'yahoo',
  'bing',
]); // 4089

// lets take three different compound stats and work out the collective sum by using absolute namespace paths
$totalReferralsAbsolute = $statsCollector->getStatsSum([
  '.website.referrals.google',
  '.website.referrals.yahoo',
  '.website.referrals.bing',
]); // 4089


// Lets count how many values there are in a namespace
// (count will return the number of values, not the sum of the values)
$googleReferralEntryCount = $statsCollector->getStatCount(".website.referrals.google"); //4

// count how many values there are in a collection of namespaces at once
$totalReferralEntries = $statsCollector->getStatsCount([
  ".website.referrals.google",
  ".website.referrals.yahoo",
  ".website.referrals.bing",
]); //10

// lets get the sum of a compound stat
$statsCollector->setNamespace("api.response")
  ->addStat("success", 23223)
  ->addStat("success", 1322)
  ->addStat("success", 7324)
  ->addStat("success", 24922)
  ->addStat("success", 94234);

$totalSuccessfulResponses = $statsCollector->getStatSum('.api.response.success'); // 151025

// lets get the combined sum of two different compound stats
$statsCollector->setNamespace("api.response")// we don't need to redeclare this unless the namespace changes
->addStat("error", 23)
  ->addStat("error", 12)
  ->addStat("error", 74)
  ->addStat("error", 49)
  ->addStat("error", 9);


$totalResponses = $statsCollector->getStatsSum([
  '.api.response.success',
  '.api.response.error',
]); // 151192


/**
 * Advanced usage. Associative arrays as stats (mapped to array-key-like output in file and metric labels in Prometheus)
 */

$winners = [
  "sprint=8s" => 5,
  'sprint=10s' => 9,
  'sprint=12s' => 21,
];

$statsCollector->setNamespace("olympics.100m")->addStat("winners", $winners);

$olympics100mWinnersByTime = $statsCollector->getStat('winners'); // Array ( [<10s] => 5 [10s-12s] => 9 [12s] => 21 )
$olympics100mTotalWinners = $statsCollector->getStatSum('winners'); // 35


/**
 * Advanced usage. Lets increment/decrement some compound stats
 */

$statsCollector->setNamespace("users")
  ->addStat("points", [10, 15, 20]);

//lets increment all compound stat values
$statsCollector->incrementCompoundStat("points", $increment = 5);

$pointsIncrementedByFive = $statsCollector->getStat("points"); // Array ( [0] => 15 [1] => 20 [2] => 25 )

//lets reset the compound stat values back to down their original values by decrementing them by 5
$statsCollector->decrementCompoundStat("points", $decrement = 5);

$pointsDecrementedByFive = $statsCollector->getStat("points"); // Array ( [0] => 10 [1] => 15 [2] => 20 )



/**
 * Extending the Stats Collector with your own subject specific instance is
 * also possible by extending the AbstractCollector
 */

// this instance of stats collector has a custom 'civi' root namespace
$CiviCRMCollector = CiviCRMCollector::getInstance();

$CiviCRMCollector->addStat("users.created", 500);
$usersCreated = $CiviCRMCollector->getStat("users.created"); // 500


/**
 * Exporting stats
 */

//export all stats collected so far to sample_stats.stats file
$exporter = new Statistics\Exporter\File("sample_stats");
$exporter->path = __DIR__ . DIRECTORY_SEPARATOR . 'out'; // output path
$exporter->export($statsCollector);

// export a bunch of targeted stats
// return as associative array of namespace=>value to pass to export() due to getWithKey() being called
$noahsArkStats = $statsCollector->getStat("noahs.ark.passengers.*", true);

// you can update $exporter->filename & $exporter->path before each export() call for a different output dir/name
$exporter->filename = "noahs_ark_stats";
$exporter->export($noahsArkStats);

//export an entire custom collector instance.  export() takes either an array of stats or an instance of AbstractCollector.
$exporter->filename = "civicrm_stats";
$exporter->export($CiviCRMCollector);

/**
 * Exporting stats to Prometheus exporter
 */

//export all stats collected so far to sample_stats.prom file
//exporter also takes care of any mapping required for output. In the case of
//Prometheus, we map dots to underscores before writing to .prom files.
$exporter = new Statistics\Exporter\Prometheus("sample_stats");
$exporter->path = __DIR__ . DIRECTORY_SEPARATOR . 'prometheus_out'; // output path
$exporter->export($statsCollector);

// export a bunch of targeted stats
// return as associative array of namespace=>value to pass to export() due to getWithKey() being called
$noahsArkStats = $statsCollector->getStat("noahs.ark.passengers.*", true);
// you can update $exporter->filename & $exporter->path before each export() call for a different output dir/name
$exporter->filename = "noahs_ark_stats";
$exporter->export($noahsArkStats);

//export an entire custom collector instance.  export() takes either an array of stats or an instance of AbstractCollector.
$exporter->filename = "civicrm_stats";
$exporter->export($CiviCRMCollector);

// checkout the resulting output for the above export code here: https://github.com/jackgleeson/stats-collector/tree/master/samples/prometheus_out
?>