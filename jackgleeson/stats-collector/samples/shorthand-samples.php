<?php
require __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
/**
 * Get an instance of the Collector
 */
$stats = Statistics\Collector\Collector::getInstance();

/**
 * Setting & Getting stats
 */

// basic usage (add to default 'root' namespace)
$stats->add("users", 45); // add stat to "general" default general namespace
$users = $stats->get("users"); // 45
$usersWithNamespaceInKey = $stats->getWithKey("users"); // Array ( [root.users] => 45 )

// define a new default namespace and add stats to it
$stats->ns("website")->add("clicks", 30)->add("banner.views", 20);
// also add a sub-namespace to the current 'website' namespace (in a relative fashion)

// get single stat by relative (resolves to website.clicks due to last set namespace being "website" on line 18)
$clicks = $stats->get("clicks"); // 30 - the get() call is relative to your last default namespace

// get single stat by sub-namespace relative (resolves to website.banner.views)
$bannerViews = $stats->get("banner.views"); // 20 - the get() call is made to website.banner.clicks

// get single stat by absolute path
$websiteClicks = $stats->get(".website.clicks"); // 30 - prepending paths with '.' resolves to an absolute path

// get multiple stats back using absolute paths
$statsAbsolute = $stats->get([
  '.website.clicks',
  '.website.banner.views',
]); // $statsAbsolute = Array ( [0] => 30 [1] => 20 )

// get multiple stats back using absolute paths including their full namespace as the key
$statsAbsoluteWithKeys = $stats->getWithKey([
  '.website.clicks',
  '.website.banner.views',
]); // $statsAbsoluteWithKeys = Array ( [website.clicks] => 30 [website.banner.views] => 20 )

// get multiple stats, one using absolute namespace and one using relative namespace
$statsRelative = $stats->get([
  'clicks',
  '.website.banner.views',
]); // Array ( [0] => 30 [1] => 20 )

//removing a stat
$stats->del('clicks');

//define a long namespace, add a stat related stats and retrieve it using a wildcard operator
$stats->ns("this.is.a.really.long.namespace.path")
  ->add("age", 33);
$clicks = $stats->get("this.*.age"); // 33

//define a namespace, add some stats and retrieve them all with wildcard paths
$stats->ns("transactions")
  ->add("mobile", 10)->add("website", 20)->add("tablet", 30)->add("other", 40);

// lets get all transaction stats using the wildcard operator
$transactions = $stats->get("transactions.*");
// $transactions = Array ( [0] => 10 [1] => 20 [2] => 30 [3] => 40 )

// lets get all transaction stats using the wildcard operator including their full namespace as the key
$transactionsWithKeys = $stats->getWithKey("transactions.*");
// $transactions = Array ( [transactions.mobile] => 10 [transactions.website] => 20 [transactions.tablet] => 30 [transactions.other] => 40 )


// get() will auto-deduplicate results if you accidentally include the same stat twice using wildcards
$transactionsWithUniqueStats = $stats->get([
  "transactions.*",
  ".transactions.mobile",
]);
// only one mobile stat of '10' is present in the result $transactionsWithUniqueStats = Array ( [0] => 10 [1] => 20 [2] => 30 [3] => 40 )

/**
 * Working with basic stats, basic functions (increment/decrement)
 */

// lets increment some stats

$daysOnEarth = (33 * 365); // 12045 added to 'general.stats.days_on_the_earth and then we increment by 1 day
$stats->ns("general.stats")
  ->add("days_on_the_earth", $daysOnEarth)->inc("days_on_the_earth", 1);
$daysOnEarth = $stats->get("days_on_the_earth"); // 12046
$daysOnEarthAbsolute = $stats->get(".general.stats.days_on_the_earth"); // same as above 12046

// lets decrement some stats

// 53 as of 11/02/2017
$stats->ns("general.other.stats")
  ->add("days_until_christmas", 53)->dec("days_until_christmas"); // skip 24 hours
$daysUntilChristmas = $stats->get("days_until_christmas"); // 52


/**
 * Working with basic stats, aggregate functions (sum/average)
 */

// lets add a bunch of stats and sum them
$stats->ns("noahs.ark.passengers")
  ->add("humans", 2)->add("aliens", 0)->add("animal.cats", 3)->add("animal.dogs", 6)->add("animal.chickens", 25);

// total number of passengers on noahs ark
$numberOfTotalPassengers = $stats->sum("noahs.ark.passengers.*"); // 36
$numberOfAnimalPassengers = $stats->sum("animal.*"); // 34

// lets sum up some individual stats
$stats->ns("visits.month")
  ->add("jan", 553)
  ->add("feb", 223)
  ->add("mar", 434)
  ->add("apr", 731)
  ->add("may", 136)
  ->add("june", 434)
  ->add("july", 321)
  ->add("aug", 353)
  ->add("sept", 657)
  ->add("oct", 575)
  ->add("nov", 1020)
  ->add("dec", 2346);

// you could use a wildcard to get the sum of visits by targeting  'visits.month.*'
$visitsForTheYearWildcard = $stats->sum(".visits.month.*"); ////7783

$averageVisitsPerMonthWildcard = $stats->avg("month.*"); //648.58333333333


/**
 * Working with compound stats (averages/sum/count)
 *
 * Stats become "compound" when you add either an array of values to a single
 * stat or when you add a stat to an already existing namespace.
 */

// lets get the average of a compound stat
$stats->ns("users")
  ->add("age", 23)->add("age", 12)->add("age", 74)->add("age", 49)->add("age", 9);

$averageAges = $stats->avg('age'); //33.4

// another way to convert to a compound stat is just to pass an array of values as the value (it will auto-flatten by default)
$stats->ns("users")
  ->add("heights", 171)
  ->add("heights", [181, 222, 194, 143, 123, 161, 184]);

$averageHeights = $stats->avg('heights'); //172.375

// clobber/overwrite existing stat when adding to prevent compound behaviour (e.g. updating timestamps)
$stats->ns("cart");
$stats->add("last_checkout_time", strtotime('-1 day', strtotime('now')));
$stats->add("last_checkout_time", strtotime('now'));
$checkoutTimes = $stats->get("last_checkout_time"); //Array ( [0] => 1510593647 [1] => 1510680047 )

$stats->clobber("last_checkout_time", strtotime('-1 day', strtotime('now')));
$stats->clobber("last_checkout_time", strtotime('now'));
$lastCheckoutTimeSingleResult = $stats->get("last_checkout_time"); //1510680136

// lets take three different compound stats and work out the collective sum
$stats->ns("website.referrals")
  ->add("google", 110)
  ->add("google", 222)
  ->add("google", 146)
  ->add("google", 125)
  ->add("yahoo", 510)
  ->add("yahoo", 148)
  ->add("yahoo", 2122)
  ->add("bing", 230)
  ->add("bing", 335)
  ->add("bing", 141);

$totalReferrals = $stats->sum([
  'google',
  'yahoo',
  'bing',
]); // 4089

// lets take three different compound stats and work out the collective sum by using absolute namespace paths
$totalReferralsAbsolute = $stats->sum([
  '.website.referrals.google',
  '.website.referrals.yahoo',
  '.website.referrals.bing',
]); // 4089


// Lets count how many values there are in a namespace
// (count will return the number of values, not the sum of the values)
$googleReferralEntryCount = $stats->count(".website.referrals.google"); //4

// count how many values there are in a collection of namespaces at once
$totalReferralEntries = $stats->count([
  ".website.referrals.google",
  ".website.referrals.yahoo",
  ".website.referrals.bing",
]); //15

// lets get the sum of a compound stat
$stats->ns("api.response")
  ->add("success", 23223)->add("success", 1322)->add("success", 7324)->add("success", 24922)->add("success", 94234);

$totalSuccessfulResponses = $stats->sum('.api.response.success'); // 151025

// lets get the combined sum of two different compound stats
$stats->ns("api.response")
  ->add("error", 23)->add("error", 12)->add("error", 74)->add("error", 49)->add("error", 9);


$totalResponses = $stats->sum([
  '.api.response.success',
  '.api.response.error',
]); // 151192

/**
 * Extending the Stats Collector with your own subject specific instance is
 * also possible by extending the AbstractCollector
 */

// this instance of stats collector has a custom 'civi' root namespace
$CiviCRMCollector = Samples\CiviCRMCollector::getInstance();

$CiviCRMCollector->add("users.created", 500);
$usersCreated = $CiviCRMCollector->get("users.created"); // 500


/**
 * Exporting stats to Prometheus exporter
 */

//export all stats collected so far to sample_stats.prom file
//exporter also takes care of any mapping required for output. In the case of
//Prometheus, we map dots to underscores before writing to .prom files.
$exporter = new Statistics\Exporter\Prometheus("sample_stats");
$exporter->path = __DIR__ . DIRECTORY_SEPARATOR . 'prometheus_out'; // output path
$exporter->export($stats);

// export a bunch of targeted stats
// return as associative array of namespace=>value to pass to export() due to getWithKey() being called
$noahsArkStats = $stats->getWithKey("noahs.ark.passengers.*");
// you can update $exporter->filename & $exporter->path before each export() call for a different output dir/name
$exporter->filename = "noahs_ark_stats";
$exporter->export($noahsArkStats);

//export an entire custom collector instance.  export() takes either an array of stats or an instance of AbstractCollector.
$exporter->filename = "civicrm_stats";
$exporter->export($CiviCRMCollector);

// checkout the resulting output for the above export code here: https://github.com/jackgleeson/stats-collector/tree/master/samples/prometheus_out
?>