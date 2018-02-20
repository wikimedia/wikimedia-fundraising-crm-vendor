# Stats Collector
[![GitHub tag](https://img.shields.io/github/tag/jackgleeson/stats-collector.svg)]()
[![Build Status](https://travis-ci.org/jackgleeson/stats-collector.svg?branch=master)](https://travis-ci.org/jackgleeson/stats-collector)
[![Coverage Status](https://coveralls.io/repos/github/jackgleeson/stats-collector/badge.svg?branch=master)](https://coveralls.io/github/jackgleeson/stats-collector?branch=master)

Record, combine, retrieve and export custom statistics and log data during the lifecycle of any PHP process. 

Once you have recorded some stats, you can create new stats from your data using traditional aggregate functions like average, count and sum. You can export your stats to an output of your choice, e.g. file, log, db, queue or other custom formats. Finally, you can then display and query the exported stats in whatever frontend you wish, e.g. grafana. 

### Features
  - Wildcard name expansion with regular expression support e.g. 
  ```$stats->get("[a-z0-9]?.*");```
  - Create stats from stats. Get the data you need in one process e.g.
   ```$stats->add("overall_total", $stats->sum("separate.totals.*"));```
  - Clear separation of responsibility across general log output and statistical log output to help you stop polluting your application logs with statistical data.

### To-do
  - Filter behaviour. ```$filteredStatsCollecter = $stats->filter($this->lessThan(50), $this->equalTo(50),...); ```
  - Import behaviour. Allow Stats Collector to import previously exported data and carry on where it left off. 
  - Add tests for helpers and improve tests by mocking collaborators
  - Add listener behaviour so that stats can be updated by updates to other stats e.g. moving averages
### Credits

* [github.com/dflydev/dflydev-dot-access-data](https://github.com/dflydev/dflydev-dot-access-data)  - dot namesapce utility

### Add Stats Collector to your project
```sh
$ composer require jackgleeson/stats-collector 
```
### Basic Usage: record, increment and retrieve a stat
```php
//get an instance of stats collector
$stats = Statistics\Collector\Collector::getInstance();

//add a stat
$stats->add("clicks", 45);

//get a stat
$clicks = $stats->get("clicks");
$clicks; // 45

//increment a stat 
$stats->inc("clicks");
$clicks = $stats->get("clicks");
$clicks; // 46
```
### Basic Usage: Custom stats namespace and wildcard operator usage
```php
$stats = Statistics\Collector\Collector::getInstance();

//add a custom namespace and add some stats to it
$stats->ns("crons.payments")
  ->add("total", 30)
  ->add("succeeded", 20)
  ->add("failed", 10);

// get payment cron stats using wildcard path
$paymentStats = $stats->getWithKey("crons.payments.*");

// $paymentStats contents
Array
(
    [crons.payments.failed] => 10
    [crons.payments.succeeded] => 20
    [crons.payments.total] => 30
)
```
### Basic Usage: Record the execution time of a process

```php
$stats = Statistics\Collector\Collector::getInstance();

$stats->start("timer");
// some lengthy process...
$stats->end("timer");
// get the exectuion time
$execution_time = $stats->diff('timer'); 

```
### Basic Usage: Export stats to file
```php
$stats = Statistics\Collector\Collector::getInstance();

//add a custom namespace and add some stats to it
$stats->ns("crons.payments")
  ->add("total", 30)
  ->add("succeeded", 20)
  ->add("failed", 10);
  
//export recorded stats to a txt file (see output below)
$exporter = new Statistics\Exporter\File("demo","outdir/dir");
$exporter->export($stats);
```
### Basic Usage: Export stats to file (output)
```sh
$ cd output/dir
$ cat demo.stats
crons.payments.failed=10
crons.payments.succeeded=20
crons.payments.total=30
```

### Aggregate Usage: Add a bunch of stats across different namespaces and sum them
```php
$stats = Statistics\Collector\Collector::getInstance();

$stats->ns("noahs.ark.passengers")
  ->add("humans", 2)
  ->add("aliens", 0)
  ->add("animal.cats", 3)
  ->add("animal.dogs", 6)
  ->add("animal.birds", 25);
  
// total number of passengers on noahs ark
$totalPassengers = $stats->sum("noahs.ark.passengers.*"); // 36
$totalAnimals = $stats->sum("*passengers.animal*"); // 34
$totalCatsAndDogs = $stats->sum("*passengers.animal.[c,d]*"); // 9
```

### Aggregate Usage: Create a compound stat and work out its average
```php
$stats = Statistics\Collector\Collector::getInstance();

$stats->ns("users")
  ->add("heights", 171)
  ->add("heights", [181, 222, 194, 143, 123, 161, 184]);

$averageHeights = $stats->avg('heights'); //172.375
```


## Checkout [samples/shorthand-samples.php](https://github.com/jackgleeson/stats-collector/blob/master/samples/shorthand-samples.php) for a complete list of available functionality in action. 