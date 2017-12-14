<?php


namespace Statistics\Collector;

use Dflydev\DotAccessData\Data as Container;
use Statistics\Exception\StatisticsCollectorException;

/**
 * Statistics Collector
 *
 * This utility is designed to allow simple namespace structured key/value
 * storage for statistics recording during the lifecycle of a request or
 * process.
 *
 * Recorded statistics can then be exported via a backend specific exporter
 * class to file, log, db, queue, other.
 *
 * Reportable stats are stored in defined namespaces. The namespace
 * structure/convention/naming is entirely up to the user e.g.
 * queue.emails.inbox , civi.user.unsubscribed, server1.website.clicks are all
 * acceptable
 *
 */
abstract class AbstractCollector implements iCollector
{

    /**
     * Singleton instances container
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * namespace separator
     */
    const SEPARATOR = '.';

    /**
     * Wildcard operator
     */
    const WILDCARD = '*';

    /**
     * @var null|string
     */
    protected $namespace = null;

    /**
     * @var string
     */
    protected $defaultNamespace = "root";

    /**
     * Container for stats data
     *
     * @var Container
     */
    protected $container;

    private $populatedNamespaces = [];


    /**
     * Add some Singleton visibility restrictions to avoid inconsistencies.
     */

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
        return [];
    }

    /**
     * It is possible this singleton will be extended to allow subject specific
     * conveniences for statistics collection e.g. a fixed default namespace of
     * "queue." in QueueStatsCollector
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public static function getInstance()
    {
        $class = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
            self::$instances[$class]->containerSetup();
        }
        return self::$instances[$class];
    }

    /**
     * Empty singleton instances.
     * This method is workaround to add singleton testability as explained here
     * https://gonzalo123.com/2012/09/24/the-reason-why-singleton-is-a-problem-with-phpunit/
     */
    public static function tearDown($all = false)
    {
        if ($all === false) {
            $class = get_called_class();
            unset(static::$instances[$class]);
        } else {
            static::$instances = [];
        }
        return true;
    }

    /**
     * Alias method for getting stats
     *
     * @see AbstractCollector::getStat()
     * @see AbstractCollector::getStats()
     *
     * @param $namespace
     * @param bool $withKeys
     * @param null $default
     *
     * @return array|mixed
     */
    public function get($namespace, $withKeys = false, $default = null)
    {
        if (is_array($namespace)) {
            return $this->getStats($namespace, $withKeys, $default);
        } else {
            return $this->getStat($namespace, $withKeys, $default);
        }
    }

    /**
     * Alias method for getting stats with keys
     *
     * @see AbstractCollector::getStat()
     * @see AbstractCollector::getStats()
     *
     * @param $namespace
     * @param null $default
     *
     * @return array|mixed
     */
    public function getWithKey($namespace, $default = null)
    {
        if (is_array($namespace)) {
            return $this->getStats($namespace, true, $default);
        } else {
            return $this->getStat($namespace, true, $default);
        }
    }

    /**
     * Alias method for adding stats
     *
     * @see AbstractCollector::addStat()
     *
     * @param $name
     * @param $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function add($name, $value, $options = [])
    {
        return $this->addStat($name, $value, $options);
    }

    /**
     * Alias method for overwriting stats
     *
     * @see AbstractCollector::addStat()
     *
     * @param $name
     * @param $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function clobber($name, $value, $options = [])
    {
        $options['clobber'] = true;
        return $this->addStat($name, $value, $options);
    }

    /**
     * Alias method for removing stats
     *
     * @see AbstractCollector::removeStat()
     *
     * @param $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function del($namespace)
    {
        return $this->removeStat($namespace);
    }

    /**
     * Alias method for incrementing stats
     *
     * @see AbstractCollector::incrementStat()
     *
     * @param $namespace
     * @param int|float $increment
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function inc($namespace, $increment = 1)
    {
        return $this->incrementStat($namespace, $increment);
    }

    /**
     * Alias method for decrementing stats
     *
     * @see AbstractCollector::decrementStat()
     *
     * @param $namespace
     * @param int|float $decrement
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function dec($namespace, $decrement = -1)
    {
        return $this->decrementStat($namespace, $decrement);
    }

    /**
     * Alias method for averaging stats
     *
     * @see AbstractCollector::getStatsAverage()
     * @see AbstractCollector::getStatAverage()
     *
     * @param $namespace
     *
     * @return float|int
     */
    public function avg($namespace)
    {
        if (is_array($namespace)) {
            return $this->getStatsAverage($namespace);
        } else {
            return $this->getStatAverage($namespace);
        }
    }

    /**
     * Alias method for getting the sum of stats
     *
     * @see AbstractCollector::getStatsSum()
     * @see AbstractCollector::getStatSum()
     *
     * @param array $namespace
     *
     * @return float|int
     */
    public function sum($namespace)
    {
        if (is_array($namespace)) {
            return $this->getStatsSum($namespace);
        } else {
            return $this->getStatSum($namespace);
        }
    }

    /**
     * Alias method for counting the number of stats for a given namespace
     *
     * @see AbstractCollector::getStatsCount()
     * @see AbstractCollector::getStatCount()
     *
     * @param $namespace
     *
     * @return int
     */
    public function count($namespace)
    {
        if (is_array($namespace)) {
            return $this->getStatsCount($namespace);
        } else {
            return $this->getStatCount($namespace);
        }
    }

    /**
     * Alias method for returning all stats
     *
     * @see AbstractCollector::getAllStats()
     * @return array
     */
    public function all()
    {
        return $this->getAllStats();
    }

    /**
     * Alias method for setting the current namespace
     *
     * @see AbstractCollector::setNamespace()
     *
     * @param $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function ns($namespace)
    {
        return $this->setNamespace($namespace);
    }

    /**
     * Record a statistic for a subject
     *
     * TODO:
     * - workout how to handle backend specific types as values
     *
     * @param string $name name of statistic to be added to namespace
     * @param mixed $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function addStat($name, $value, $options = [])
    {
        // we auto-flatten any multi-dimensional arrays
        if (!array_key_exists("flatten", $options)) {
            $options['flatten'] = true;
        }

        // if true, we overwrite existing value
        if (!array_key_exists("clobber", $options)) {
            $options['clobber'] = false;
        }

        $this->addValueToNamespace($name, $value, $options);
        return $this;
    }

    /**
     * Delete a statistic
     *
     * @param string $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function removeStat($namespace)
    {
        if (strpos($namespace, static::WILDCARD) !== false) {
            throw new StatisticsCollectorException("Wildcard usage forbidden when removing stats (to protect you from yourself!)");
        }

        if ($this->checkExists($namespace) === true) {
            $this->removeValueFromNamespace($namespace);
        } else {
            throw new StatisticsCollectorException("Attempting to remove a statistic that does not exist: " . $namespace);
        }
        return $this;
    }

    /**
     * Increment a statistic
     *
     * @param string $namespace
     * @param int|float $increment
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function incrementStat($namespace, $increment = 1)
    {
        if ($this->checkExists($namespace) !== true) {
            $this->addStat($namespace, 0);
        }

        $currentValue = $this->getStat($namespace);
        if ($this->is_incrementable($currentValue)) {
            $this->updateValueAtNamespace($namespace,
              $currentValue + $increment);
            return $this;
        } else {
            throw new StatisticsCollectorException("Attempted to increment a value which cannot be incremented! (" . $namespace . ":" . gettype($currentValue) . ")");
        }
    }


    /**
     * Decrement a statistic
     *
     * @param $namespace
     * @param int|float $decrement
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function decrementStat($namespace, $decrement = -1)
    {
        if ($this->checkExists($namespace) !== true) {
            $this->addStat($namespace, 0);
        }

        $currentValue = $this->getStat($namespace);
        if ($this->is_incrementable($currentValue)) {
            $this->updateValueAtNamespace($namespace,
              $currentValue - abs($decrement));
            return $this;
        } else {
            throw new StatisticsCollectorException("Attempted to decrement a value which cannot be decremented! (" . $namespace . ":" . gettype($currentValue) . ")");
        }
    }


    /**
     * Retrieve statistic for a given namespace
     *
     * @param string $namespace
     * @param bool $withKeys
     * @param mixed $default default value to be returned if stat $namespace
     *   empty
     *
     * @return mixed
     */
    public function getStat($namespace, $withKeys = false, $default = null)
    {
        // send wildcards and multi-namespaces to the plural method
        if (strpos($namespace, static::WILDCARD) !== false ||
          is_array($namespace)
        ) {
            return $this->getStats([$namespace], $withKeys, $default);
        }

        if ($this->checkExists($namespace) === true) {
            $resolvedNamespace = $this->getTargetNamespaces($namespace);

            if ($withKeys === true) {
                $value[$resolvedNamespace] = $this->getValueFromNamespace($namespace);
            } else {
                $value = $this->getValueFromNamespace($namespace);
            }
        } else {
            if ($withKeys === true) {
                $value[$namespace] = $default;
            } else {
                $value = $default;
            }
        }
        return $value;

    }

    /**
     * Retrieve a collection of statistics with an array of subject namespaces
     *
     * @param array $namespaces
     * @param bool $withKeys
     * @param mixed $default default value to be returned if stat $namespace
     *   empty
     *
     * @return array
     */
    public function getStats(
      array $namespaces,
      $withKeys = false,
      $default = null
    ) {
        $resolvedNamespaces = $this->getTargetNamespaces($namespaces, true);
        if (!is_array($resolvedNamespaces)) {
            $resolvedNamespaces = [$resolvedNamespaces];
        }

        //iterate over $namespaces and retrieve values
        $stats = [];
        foreach ($resolvedNamespaces as $namespace) {
            $stat = $this->getStat($namespace, $withKeys, $default);
            $stats = array_merge($stats, (is_array($stat) ? $stat : [$stat]));
        }
        return $stats;
    }

    /**
     * Count the number of values recorded for a given stat
     *
     * @param $namespace
     *
     * @return int
     */
    public function getStatCount($namespace)
    {
        $value = $this->getStat($namespace);
        return count($value);
    }

    /**
     * Count the number of values recorded for a collection of given stats
     *
     * @param array $namespaces
     *
     * @return int
     * @internal param array $names
     */
    public function getStatsCount(array $namespaces)
    {
        $count = 0;
        foreach ($namespaces as $namespace) {
            $count += $this->getStatCount($namespace);
        }
        return $count;
    }

    /**
     * @param $namespace
     *
     * @return float|int
     */
    public function getStatAverage($namespace)
    {
        $value = $this->getStat($namespace);
        return $this->calculateStatsAverage($value);
    }

    /**
     * @param array $namespaces
     *
     * @return float|int
     */
    public function getStatsAverage(array $namespaces)
    {
        $allStats = [];
        foreach ($namespaces as $namespace) {
            $value = $this->getStat($namespace);
            if (!is_array($value)) {
                $value = [$value];
            }
            $allStats = array_merge($allStats, $value);
        }
        return $this->calculateStatsAverage($allStats);

    }

    /**
     * @param $namespace
     *
     * @return float|int
     */
    public function getStatSum($namespace)
    {
        $value = $this->getStat($namespace);
        return $this->calculateStatsSum($value);
    }

    /**
     * @param array $namespaces
     *
     * @return float|int
     */
    public function getStatsSum(array $namespaces)
    {
        $totalSum = [];
        foreach ($namespaces as $namespace) {
            $values = $this->getStat($namespace);
            if (!is_array($values)) {
                $values = [$values];
            }
            $totalSum = array_merge($totalSum, $values);
        }
        return $this->calculateStatsSum($totalSum);
    }

    /**
     *  Retrieve statistics for all subject namespaces
     *
     * @return array
     * @throws StatisticsCollectorException
     */
    public function getAllStats()
    {
        $data = [];
        foreach ($this->populatedNamespaces as $namespace) {
            $data[$namespace] = $this->container->get($namespace);
        }
        return $data;
    }

    /**
     * @param $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function setNamespace($namespace)
    {
        return $this->setCurrentNamespace($namespace);

    }

    /**
     * Return the current namespace. Default to default namespace if none set.
     *
     * @return string
     */
    public function getCurrentNamespace()
    {
        return ($this->namespace === null) ? $this->getDefaultNamespace() : $this->namespace;
    }


    /**
     * @return array
     */
    protected function getPopulatedNamespaces()
    {
        return $this->populatedNamespaces;
    }

    /**
     * TODO:
     * - validate namespace argument
     *
     * @param $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    protected function setCurrentNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return mixed
     */
    protected function resolveWildcardNamespace($namespace)
    {
        // clear absolute path initial '.' as not needed for wildcard
        if (strpos($namespace, static::SEPARATOR) === 0) {
            $namespace = $target = substr($namespace, 1);
        }

        // add a additional namespace route by prepending the current parent ns to the wildcard query
        // handle relative and absolute wildcard searching
        $additionalNamespace = $this->getCurrentNamespace() . "." . $namespace;

        $expandedPaths = [];
        foreach ($this->getPopulatedNamespaces() as $populatedNamespace) {
            if (fnmatch($namespace, $populatedNamespace) || fnmatch($additionalNamespace, $populatedNamespace)) {
                // we convert the expanded wildcard paths to absolute paths by prepending '.'
                // this prevents the getTargetNamespaces() from treating the namespace as a sub namespace
                $expandedPaths[] = static::SEPARATOR . $populatedNamespace;
            }
        }

        return $expandedPaths;
    }

    /**
     * Determine the target namespace(s) based on the namespace value(s)
     * '.' present at beginning indicates absolute namespace path
     * '.' present but not at the beginning indicates branch namespace path of
     * the current namespace
     * '.' not present indicates leaf-node namespace of current namespace
     * '*' present indicates wildcard namespace path expansion required
     *
     * @param mixed $namespaces
     * @param bool $returnAbsolute
     *
     * @return mixed $resolvedNamespaces
     */
    protected function getTargetNamespaces($namespaces, $returnAbsolute = false)
    {
        if (!is_array($namespaces)) {
            $namespaces = [$namespaces];
        }

        $resolvedNamespaces = [];
        foreach ($namespaces as $namespace) {
            if (strpos($namespace, static::WILDCARD) !== false) {
                // wildcard
                $wildcardPaths = $this->resolveWildcardNamespace($namespace);
                $resolvedNamespaces = array_merge($resolvedNamespaces,
                  $wildcardPaths);
            } else {
                // non-wildcard
                if (strpos($namespace, static::SEPARATOR) === 0) {
                    // absolute path namespace e.g. '.this.a.full.path.beginning.with.separator'
                    $resolvedNamespaces[] = ($returnAbsolute === false) ? substr($namespace,
                      1) : $namespace;
                } else {
                    // leaf-node namespace of current namespace e.g. 'dates' or
                    // sub-namespace e.g 'sub.path.of.current.namespace'
                    $resolvedNamespaces[] = ($returnAbsolute === false) ?
                      $this->getCurrentNamespace() . static::SEPARATOR . $namespace :
                      static::SEPARATOR . $this->getCurrentNamespace() . static::SEPARATOR . $namespace;
                }
            }
        }

        return (count($resolvedNamespaces) === 1) ? $resolvedNamespaces[0] : array_unique($resolvedNamespaces);
    }

    /**
     * TODO: split this method into smaller methods.
     *
     * @param string $namespace
     * @param mixed $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    protected function addValueToNamespace($namespace, $value, $options)
    {
        $flatten = false;
        if (array_key_exists("flatten", $options) &&
          $options['flatten'] === true &&
          is_array($value)
        ) {
            $flatten = true;
            $flattenedValues = $this->arrayFlatten($value);
        }

        $clobber = false;
        if (array_key_exists("clobber", $options) &&
          $options['clobber'] === true
        ) {
            $clobber = true;
        }

        $targetNS = $this->getTargetNamespaces($namespace);

        // if value exists and is not to be overwritten(clobbered)
        if ($this->container->has($targetNS) && ($clobber === false)) {
            // need to combine the flatten behaviour and move into flatten add method
            if ($flatten === true) {
                $currentValue = $this->container->get($targetNS);
                $values = (is_array($currentValue)) ?
                  array_merge($currentValue,
                    $flattenedValues) : array_merge([$currentValue],
                    $flattenedValues);
                $this->container->set($targetNS, $values);
            } else {
                $this->container->append($targetNS, $value);
            }
        } else {
            // if value doesn't exist or clobber=true, create new entry with value
            if ($flatten === true) {
                $this->container->set($targetNS, $flattenedValues);
            } else {
                $this->container->set($targetNS, $value);
            }
            $this->addPopulatedNamespace($targetNS);
        }
        return $this;
    }

    /**
     * @param $namespace
     * @param $value
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     * @internal param $name
     */
    protected function updateValueAtNamespace($namespace, $value)
    {
        $targetNS = $this->getTargetNamespaces($namespace);
        if ($this->container->has($targetNS)) {
            $this->container->set($targetNS, $value);
        } else {
            throw new StatisticsCollectorException("Unable to update value at " . $targetNS);
        }
        return $this;
    }

    /**
     * @param $name
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    protected function removeValueFromNamespace($name)
    {
        $targetNS = $this->getTargetNamespaces($name);
        $this->container->remove($targetNS);
        $this->removePopulatedNamespace($targetNS);
        return $this;
    }

    /**
     * Retrieve stats value from container, return null if not found.
     *
     * @param $name
     *
     * @return mixed
     */
    protected function getValueFromNamespace($name)
    {
        $targetNS = $this->getTargetNamespaces($name);
        return $this->container->get($targetNS);
    }

    /**
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    /**
     * Check to see if value can be incremented.
     * Currently PHP only allows numbers and strings to be incremented. We only
     * want numbers
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function is_incrementable($value)
    {
        return (is_int($value) || is_float($value));
    }

    /**
     * Keep track of populated namespaces
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function addPopulatedNamespace($namespace)
    {
        array_push($this->populatedNamespaces, $namespace);
        $this->sortPopulatedNamespaces();
        return true;
    }

    /**
     * Remove a namespace from the populated namespaces array (typically when
     * it becomes empty)
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function removePopulatedNamespace($namespace)
    {
        if (($index = array_search($namespace,
            $this->populatedNamespaces)) !== false) {
            unset($this->populatedNamespaces[$index]);
            $this->sortPopulatedNamespaces();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check that namespace element(s) exist
     *
     * TODO: write the value of the non-existent namespaces for arrays of
     * namespaces checks, out somewhere. It would be frustrating if 9/10 were valid
     * but due to the 1 non-existent, the check fails and you don't know why.
     *
     * @param mixed $namespace
     *
     * @return bool
     */
    protected function checkExists($namespace)
    {
        $resolvedNamespace = $this->getTargetNamespaces($namespace);
        if (is_array($resolvedNamespace)) {
            foreach ($resolvedNamespace as $ns) {
                if (!$this->container->has($ns)) {
                    return false;
                }
            }
        } else {
            if (!$this->container->has($resolvedNamespace)) {
                return false;
            }
        }
        return true;
    }

    protected function calculateStatsSum($stats)
    {
        if ($this->is_summable($stats)) {
            switch (gettype($stats)) {
                case "integer":
                case "double":
                    return $stats;
                case "array":
                    return $this->summate($stats);
                default:
                    throw new StatisticsCollectorException("Unable to return sum for this collection of values (are they all numbers?)");
            }
        } else {
            throw new StatisticsCollectorException("Unable to return sum for this collection of values (are they all numbers?)");
        }

    }

    protected function calculateStatsAverage($stats)
    {
        if ($this->is_averageable($stats)) {
            switch (gettype($stats)) {
                case "integer":
                case "double":
                    return $stats;
                case "array":
                    return $this->average($stats);
                default:
                    throw new StatisticsCollectorException("Unable to return average for this collection of values (are they all numbers?)");
            }
        } else {
            throw new StatisticsCollectorException("Unable to return average for this collection of values (are they all numbers?)");
        }
    }

    /**
     * sort namespaces into groups by namespace level size alphabetical order
     *
     * @return bool
     */
    protected function sortPopulatedNamespaces()
    {
        sort($this->populatedNamespaces, SORT_NATURAL);
        usort($this->populatedNamespaces, function ($a, $b) {
            return strnatcmp(substr_count($a, '.'), substr_count($b, '.'));
        });
        return true;
    }

    /**
     * TODO:
     * - this is the same as is averageable(). refactor both into one method?
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function is_summable($value)
    {
        switch (gettype($value)) {
            case "integer":
            case "double":
                return true;
            case "array":
                foreach ($value as $v) {
                    if ($this->is_summable($v) === false) {
                        return false;
                    }
                }
                return true;
            default:
                return false;
        }
    }

    /**
     * Check if value is a number or a collection of numbers available to
     * averaged.
     *
     * TODO:
     * - work out how to prevent subnamespaces of the current breaking current
     * averaging
     *
     * @param $value
     *
     * @return bool
     */
    protected function is_averageable($value)
    {
        switch (gettype($value)) {
            case "integer":
            case "double":
                return true;
            case "array":
                foreach ($value as $v) {
                    if ($this->is_averageable($v) === false) {
                        return false;
                    }
                }
                return true;
            default:
                return false;
        }
    }

    /**
     * Get the average of a collection of values
     *
     * @param array $values
     *
     * @return float|int
     */
    protected function average($values = [])
    {
        return (count($values) > 0) ? array_sum($values) / count($values) : 0;
    }

    /**
     * Get the sum of a collection of values
     *
     * @param array $values
     *
     * @return float|int
     */
    protected function summate($values = [])
    {
        return array_sum($values);
    }

    /**
     * Flatten a multi-dimensional array down to a single array
     *
     * @param array $array
     *
     * @return array
     */
    protected function arrayFlatten($array = [])
    {
        $flattened = [];
        array_walk_recursive($array, function ($a) use (&$flattened) {
            $flattened[] = $a;
        });
        return $flattened;
    }

    /**
     * During getInstance() we want to configure the container to be an
     * instance of Container()
     */
    protected function containerSetup()
    {
        if (!$this->container instanceof Container) {
            $this->container = new Container();
        }
    }

}