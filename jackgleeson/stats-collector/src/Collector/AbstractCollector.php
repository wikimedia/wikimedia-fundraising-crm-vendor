<?php

namespace Statistics\Collector;

use Dflydev\DotAccessData\Data as Container;
use Statistics\Helper\ArrayHelper;
use Statistics\Helper\MathHelper;
use Statistics\Helper\TypeHelper;
use Statistics\Collector\Traits\CollectorShorthand;
use Statistics\Collector\Traits\SingletonInheritance;
use Statistics\Exception\StatisticsCollectorException;

abstract class AbstractCollector implements iCollector, iCollectorShorthand, iSingleton
{

    use CollectorShorthand, SingletonInheritance;

    /**
     * Namespace separator
     */
    const SEPARATOR = '.';

    /**
     * Wildcard operator
     */
    const WILDCARD = '*';

    /**
     * default group namespace for timers
     */
    const TIMERS_NS = 'timer';

    /**
     * @var null|string
     */
    protected $namespace = null;

    /**
     * Container for stats data
     *
     * @var Container
     */
    protected $container;

    /**
     * Array of populated leaf node paths
     *
     * @var array
     */
    protected $populatedNamespaces = [];

    /**
     * Record a statistic to a namespace
     *
     *
     * @param string $name name of statistic namespace
     * @param mixed $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function addStat($name, $value, $options = [])
    {
        $options = array_merge($this->getDefaultAddValueToNamespaceOptions(), $options);
        $this->addValueToNamespace($name, $value, $options);
        return $this;
    }

    /**
     * Remove a statistic namespace
     *
     * @param string $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function removeStat($namespace)
    {
        if ($this->isWildcardNamespace($namespace)) {
            throw new StatisticsCollectorException("Wildcard usage forbidden when removing stats (to protect you from yourself!)");
        }

        if ($this->exists($namespace) === true) {
            $this->removeValueFromNamespace($namespace);
        } else {
            throw new StatisticsCollectorException("Attempting to remove a statistic that does not exist: " . $namespace);
        }
        return $this;
    }

    /**
     * Check that a stats namespace exists
     *
     * @param string $namespace
     *
     * @return bool
     */
    public function exists($namespace)
    {
        $resolvedNamespaces = $this->getTargetNamespaces($namespace, false);
        if ((new TypeHelper())->isArray($resolvedNamespaces)) {
            if (count($resolvedNamespaces) > 0) {
                foreach ($resolvedNamespaces as $namespace) {
                    if ($this->getStatsContainer()->has($namespace) === false) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return $this->getStatsContainer()->has($resolvedNamespaces);
        }
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
        if ($this->exists($namespace) !== true) {
            $this->addStat($namespace, 0);
        }

        $currentValue = $this->getStat($namespace);

        if ($this->isIncrementable($currentValue)) {
            $options['clobber'] = true;
            $this->addStat($namespace, $currentValue + $increment, $options);
            return $this;
        } else {
            throw new StatisticsCollectorException("Attempted to increment a value which cannot be incremented! (" . $namespace . ":" . gettype($currentValue) . ")");
        }
    }

    /**
     * Increment the values of a compound stat either by supplying:
     * 1) a single int or float to be incremented across all stat values
     * 2) key=>value array to be used to increment specific compound stat values with a specific increment
     *
     * @param string $namespace
     * @param int|float|array $increment default 1
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function incrementCompoundStat($namespace, $increment = 1)
    {
        $typeHelper = new TypeHelper();

        //if the namespace doesn't exist we create the correct compound stat with values of '0'
        if ($this->exists($namespace) !== true) {
            if ($typeHelper->isArray($increment)) {
                // if array of increments is supplied we create a default compound stat using array keys with value of '0'
                $default = array_fill_keys(array_keys($increment), 0);
            } else {
                //if float|int is supplied we create default compound stat with value of '0'
                $default = [0];
            }
            $this->addStat($namespace, $default);
        }

        // get current compound stat values to check it is compound and if so begin incrementing
        $currentValues = $this->getStat($namespace);
        if (!$typeHelper->isArray($currentValues)) {
            throw new StatisticsCollectorException("The stat you are trying to increment is not a compound stat, instead use incrementStat(). $namespace=$currentValues(" . gettype($currentValues) . ")");
        }

        // check we have either an int|float or an array of int|floats.
        if ($typeHelper->isIntOrFloatRecursive($increment)) {
            if ($typeHelper->isArray($increment)) {
                // if $increment is an array, we match (or add) the keys in $increment to the keys in $currentValues and
                // increment $currentValues[$increment_key] with the corresponding $increment value.
                $keys = array_keys($increment);
            } else {
                // if $increment is an int|float, we loop through the $currentValues and increment all values
                // with the value of $increment
                $keys = array_keys($currentValues);
            }

            for ($i = 0; $i < count($keys); $i++) {
                // if key is not set, we add it with a default of '0'
                if (!array_key_exists($keys[$i], $currentValues)) {
                    $currentValues[$keys[$i]] = 0;
                }

                if ($this->isIncrementable($currentValues[$keys[$i]])) {
                    $incrementer = ($typeHelper->isArray($increment)) ? $increment[$keys[$i]] : $increment;
                    $currentValues[$keys[$i]] += $incrementer;
                } else {
                    throw new StatisticsCollectorException("Attempting to increment a compound value which cannot be incremented! (" . $namespace . "[" . $keys[$i] . "]=\"" . $currentValues[$keys[$i]] . "\":" . gettype($currentValues[$keys[$i]]) . ")");
                }
            }
        } else {
            throw new StatisticsCollectorException("Attempting to increment a compound stat with a value which is not a float or integer!");
        }

        // finally assign the new compound stat values to the stat namespace
        $updatedCompoundStat = $currentValues;
        $options['clobber'] = true;
        $this->addStat($namespace, $updatedCompoundStat, $options);
        return $this;
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
        if ($this->exists($namespace) !== true) {
            $this->addStat($namespace, 0);
        }

        $currentValue = $this->getStat($namespace);

        if ($this->isDecrementable($currentValue)) {
            $options['clobber'] = true;
            $this->addStat($namespace, $currentValue - abs($decrement), $options);
            return $this;
        } else {
            throw new StatisticsCollectorException("Attempted to decrement a value which cannot be decremented! (" . $namespace . ":" . gettype($currentValue) . ")");
        }
    }

    /**
     * Decrement the values of a compound stat either by supplying:
     * 1) a single int or float to be decremented across all stat values
     * 2) key=>value array to be used to decrement specific compound stat values with a specific decrement
     *
     * @param string $namespace
     * @param int|float|array $decrement default 1
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws StatisticsCollectorException
     */
    public function decrementCompoundStat($namespace, $decrement = 1)
    {
        $typeHelper = new TypeHelper();

        //if the namespace doesn't exist we create the correct compound stat with values of '0'
        if ($this->exists($namespace) !== true) {
            if ($typeHelper->isArray($decrement)) {
                // if array of decrements is supplied we create a default compound stat using array keys with value of '0'
                $default = array_fill_keys(array_keys($decrement), 0);
            } else {
                //if float|int is supplied we create default compound stat with value of '0'
                $default = [0];
            }
            $this->addStat($namespace, $default);
        }

        // get current compound stat values to check it is compound and if so begin decrementing
        $currentValues = $this->getStat($namespace);
        if (!$typeHelper->isArray($currentValues)) {
            throw new StatisticsCollectorException("The stat you are trying to decrement is not a compound stat, instead use decrementStat(). $namespace=$currentValues(" . gettype($currentValues) . ")");
        }

        // check we have either an int|float or an array of int|floats.
        if ($typeHelper->isIntOrFloatRecursive($decrement)) {
            if ($typeHelper->isArray($decrement)) {
                // if $decrement is an array, we match (or add) the keys in $decrement to the keys in $currentValues and
                // decrement $currentValues[$decrement_key] with the corresponding $decrement value.
                $keys = array_keys($decrement);
            } else {
                // if $decrement is an int|float, we loop through the $currentValues and decrement all values
                // with the value of $decrement
                $keys = array_keys($currentValues);
            }

            for ($i = 0; $i < count($keys); $i++) {
                // if key is not set, we add it with a default of '0'
                if (!array_key_exists($keys[$i], $currentValues)) {
                    $currentValues[$keys[$i]] = 0;
                }

                if ($this->isdecrementable($currentValues[$keys[$i]])) {
                    $decrementer = ($typeHelper->isArray($decrement)) ? $decrement[$keys[$i]] : $decrement;
                    $currentValues[$keys[$i]] -= abs($decrementer);
                } else {
                    throw new StatisticsCollectorException("Attempting to decrement a compound value which cannot be decremented! (" . $namespace . "[" . $keys[$i] . "]=\"" . $currentValues[$keys[$i]] . "\":" . gettype($currentValues[$keys[$i]]) . ")");
                }
            }
        } else {
            throw new StatisticsCollectorException("Attempting to decrement a compound stat with a value which is not a float or integer!");
        }

        // finally assign the new compound stat values to the stat namespace
        $updatedCompoundStat = $currentValues;
        $options['clobber'] = true;
        $this->addStat($namespace, $updatedCompoundStat, $options);
        return $this;
    }

    /**
     * Record a timestamp to serve as the start of a time period to be measured
     *
     * @param $namespace
     * @param mixed $customTimestamp
     * @param bool $useTimerNamespacePrefix
     */
    public function startTimer($namespace, $customTimestamp = null, $useTimerNamespacePrefix = true)
    {
        if ($customTimestamp === null) {
            $start = microtime(true);
        } else {
            $start = $customTimestamp;
        }
        $namespace = ($useTimerNamespacePrefix === true) ? static::TIMERS_NS . static::SEPARATOR . $namespace : $namespace;
        $this->addStat($namespace, ['start' => $start]);
    }

    /**
     * Record a timestamp to serve as the end of a pre-existing time-period and then calculate the difference between
     * the two timestamps recorded
     *
     * @param $namespace
     * @param mixed $customTimestamp
     * @param bool $useTimerNamespacePrefix
     *
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function endTimer($namespace, $customTimestamp = null, $useTimerNamespacePrefix = true)
    {
        if ($customTimestamp === null) {
            $end = microtime(true);
        } else {
            $end = $customTimestamp;
        }

        $namespace = ($useTimerNamespacePrefix === true) ? static::TIMERS_NS . static::SEPARATOR . $namespace : $namespace;
        if ($this->hasStartTimer($namespace)) {
            $this->addStat($namespace, [
              'end' => $end,
              'diff' => $end - $this->getStat($namespace)['start'],
            ]);
        } else {
            throw new StatisticsCollectorException("Unable to find start timestamp for \"$namespace\"");
        }
    }

    /**
     * Return the difference of a recorded start and end timestamp (including microseconds)
     *
     * @param $namespace
     * @param bool $useTimerNamespacePrefix
     *
     * @return float time difference
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function getTimerDiff($namespace, $useTimerNamespacePrefix = true)
    {
        $namespace = ($useTimerNamespacePrefix === true) ? static::TIMERS_NS . static::SEPARATOR . $namespace : $namespace;
        if ($this->hasTimerDiff($namespace)) {
            return $this->getStat($namespace)['diff'];
        } else {
            throw new StatisticsCollectorException("Unable to find timer difference for \"$namespace\". Have you recorded a start timer and end timer for this stat?");
        }
    }

    /**
     * Retrieve the statistic value for a given namespace.
     *
     * Wildcard searches and arrays of namespace targets will be forwarded to getStats()
     *
     * @param mixed $namespace
     * @param bool $withKeys
     * @param mixed $default default value to be returned if stat $namespace is empty
     *
     * @see \Statistics\Collector\AbstractCollector::getStats()
     * @return mixed
     */
    public function getStat($namespace, $withKeys = false, $default = null)
    {
        if (is_array($namespace)) {
            return $this->getStats($namespace, $withKeys, $default);
        }

        if ($this->isWildcardNamespace($namespace)) {
            return $this->getStats([$namespace], $withKeys, $default);
        }

        if ($this->exists($namespace) === true) {
            if ($withKeys === true) {
                $resolvedNamespace = $this->getTargetNamespaces($namespace);
                //clear the prepended '.' on any absolute paths for keys readability
                if (strpos($resolvedNamespace, static::SEPARATOR) === 0) {
                    $resolvedNamespace = substr($resolvedNamespace, 1);
                }
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
     * Retrieve a collection of statistics for an array of given namespaces
     *
     * @param array $namespaces
     * @param bool $withKeys
     * @param mixed $default default value to be returned if stat $namespace is empty
     *
     * @return mixed
     */
    public function getStats(array $namespaces, $withKeys = false, $default = null)
    {
        $resolvedNamespaces = $this->getTargetNamespaces($namespaces, true);
        if ((new TypeHelper())->isNotArray($resolvedNamespaces)) {
            $resolvedNamespaces = [$resolvedNamespaces];
        }

        $stats = [];
        foreach ($resolvedNamespaces as $namespace) {
            $stat = $this->getStat($namespace, $withKeys, $default);
            if ($withKeys === true) {
                $stats = array_merge_recursive($stats,$stat);
            } else {
                $stats[] = $stat;
            }
        }

        if (count($stats) === 0) {
            if (count($namespaces) > 1) {
                if ($withKeys === false) {
                    return array_fill(0, count($namespaces), $default);
                } else {
                    array_fill_keys(array_keys($namespaces), $default);
                }
            } else {
                return $default;
            }
        }

        if (count($stats) === 1 && $withKeys === false) {
            return array_values($stats)[0];
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
        if ((new TypeHelper())->isArray($value)) {
            $value = (new ArrayHelper())->flatten($value);
        }
        return (new MathHelper)->count($value);
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
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function getStatAverage($namespace)
    {
        try {
            $value = $this->getStat($namespace);
            if ((new TypeHelper())->isArray($value)) {
                $value = (new ArrayHelper())->flatten($value);
            }
            return $this->calculateStatsAverage($value);
        } catch (StatisticsCollectorException $e) {
            throw new StatisticsCollectorException(
              "An error occurred with \"" . $namespace . "\" - " . ($e->getMessage()),
              $e->getCode(),
              $e
            );
        }
    }

    /**
     * @param array $namespaces
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function getStatsAverage(array $namespaces)
    {
        try {
            $allStats = [];
            foreach ($namespaces as $namespace) {
                $value = $this->getStat($namespace);
                if (!is_array($value)) {
                    $value = [$value];
                }
                $allStats = array_merge($allStats, $value);
            }
            return $this->calculateStatsAverage($allStats);
        } catch (StatisticsCollectorException $e) {
            $namespaceString = '';
            foreach ($namespaces as $namespace) {
                $namespaceString .= "\"" . $namespace . "\",";
            }
            throw new StatisticsCollectorException(
              "An error occurred with " . substr($namespaceString, 0, -1) . " - " . $e->getMessage(),
              $e->getCode(),
              $e
            );
        }
    }

    /**
     * @param $namespace
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function getStatSum($namespace)
    {
        try {
            $value = $this->getStat($namespace);
            if ((new TypeHelper())->isArray($value)) {
                $value = (new ArrayHelper())->flatten($value);
            }
            return $this->calculateStatsSum($value);
        } catch (StatisticsCollectorException $e) {
            throw new StatisticsCollectorException(
              "An error occurred with \"" . $namespace . "\" - " . $e->getMessage(),
              $e->getCode(),
              $e
            );
        }
    }

    /**
     * @param array $namespaces
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function getStatsSum(array $namespaces)
    {
        try {
            $totalSum = [];
            foreach ($namespaces as $namespace) {
                $values = $this->getStat($namespace);
                if (!is_array($values)) {
                    $values = [$values];
                }
                $totalSum = array_merge($totalSum, $values);
            }
            return $this->calculateStatsSum($totalSum);
        } catch (StatisticsCollectorException $e) {
            $namespaceString = '';
            foreach ($namespaces as $namespace) {
                $namespaceString .= "\"" . $namespace . "\",";
            }
            throw new StatisticsCollectorException(
              "An error occurred with " . substr($namespaceString, 0, -1) . " - " . $e->getMessage(),
              $e->getCode(),
              $e
            );
        }
    }

    /**
     *  Retrieve all recorded statistics.
     *
     * @return array $stats array of stats with full namespace as key
     */
    public function getAllStats()
    {
        $stats = [];
        foreach ($this->populatedNamespaces as $namespace) {
            $stats[$namespace] = $this->getStatsContainer()->get($namespace);
        }
        return $stats;
    }

    /**
     * TODO: validate namespace argument
     *
     * @param $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Return the current namespace. Default to default namespace if none set.
     *
     * @return string
     */
    public function getNamespace()
    {
        return ($this->namespace === null) ? $this->getDefaultNamespace() : $this->namespace;
    }

    /**
     * Set the default root namespace for statistics to be stored within if a custom namespace is not set.
     *
     * @return string
     */
    abstract protected function getDefaultNamespace();

    /**
     * Get the default $options values to be used in conjunction with addValueToNamespace()
     *
     * Defaults:
     * - flatten=true (reduce multi-dimensional arrays to a single array)
     * - clobber=false (assigning values to an existing stat results in the value being appended to and does not
     * overwrite).
     *
     * @see \Statistics\Collector\AbstractCollector::addValueToNamespace()
     * @return array
     */
    protected function getDefaultAddValueToNamespaceOptions()
    {
        $options = [
          'flatten' => true,
          'clobber' => false,
        ];
        return $options;
    }

    /**
     * Determine whether a namespace contains a wildcard operator
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function isWildcardNamespace($namespace)
    {
        return (strpos($namespace, static::WILDCARD) !== false);
    }

    /**
     * Determine whether a namespace contains an absolute path indicated by the fist character being a
     * separator '.' value
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function isAbsolutePathNamespace($namespace)
    {
        return (strpos($namespace, static::SEPARATOR) === 0);
    }

    /**
     * Return an array of populated leaf node paths
     *
     * @return array
     */
    protected function getPopulatedNamespaces()
    {
        return $this->populatedNamespaces;
    }

    /**
     * @param string $namespace
     *
     * @return mixed
     */
    protected function resolveWildcardNamespace($namespace)
    {
        // clear absolute path initial '.' as not needed for wildcard
        if ($this->isAbsolutePathNamespace($namespace)) {
            $namespace = $target = substr($namespace, 1);
        }

        // add a additional namespace route by prepending the current parent ns to the wildcard query to
        // handle relative and absolute wildcard searching
        $additionalNamespace = $this->getNamespace() . "." . $namespace;

        $expandedPaths = [];
        foreach ($this->getPopulatedNamespaces() as $populatedNamespace) {
            if (fnmatch($namespace, $populatedNamespace) || fnmatch($additionalNamespace, $populatedNamespace)) {
                // we convert the expanded wildcard paths to absolute paths by prepending '.'
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
        $resolvedNamespaces = [];
        if (!is_array($namespaces)) {
            $namespaces = [$namespaces];
        }

        foreach ($namespaces as $namespace) {
            switch (true) {
                case $this->isWildcardNamespace($namespace):
                    $expandedWildcardPaths = $this->resolveWildcardNamespace($namespace);
                    if (count($expandedWildcardPaths) > 0) {
                        $expandedWildcardPaths = array_map(function ($path) use ($returnAbsolute) {
                            return ($returnAbsolute === false) ? substr($path, 1) : $path;
                        }, $expandedWildcardPaths);
                    }
                    $resolvedNamespaces = array_merge($resolvedNamespaces, $expandedWildcardPaths);
                    break;
                case $this->isAbsolutePathNamespace($namespace):
                    $resolvedNamespaces[] = ($returnAbsolute === false) ? substr($namespace, 1) : $namespace;
                    break;
                default:
                    // leaf-node of current namespace e.g. 'dates' or sub-namespace e.g 'sub.path.of.current.namespace'
                    $expandedRelativeNodeNamespace = $this->getNamespace() . static::SEPARATOR . $namespace;
                    $resolvedNamespaces[] = ($returnAbsolute === false) ? $expandedRelativeNodeNamespace :
                      static::SEPARATOR . $expandedRelativeNodeNamespace;
            }
        }

        if (count($resolvedNamespaces) === 1) {
            return $resolvedNamespaces[0];
        } else {
            return array_unique($resolvedNamespaces);
        }
    }

    /**
     * Add value(s) to a namespace.
     *
     * If the namespace exists, the value will either be appended to or overwritten depending on $options['clobber']
     * If the namespace is new, the value will be stored at the target namespace
     *
     * If $options['flatten'] is set to true, multi-dimensional arrays will be flattened to one array.
     *
     * @param string $namespace
     * @param mixed $value
     * @param array $options
     *
     * @return \Statistics\Collector\AbstractCollector
     */
    protected function addValueToNamespace($namespace, $value, $options)
    {
        $flatten = $options['flatten'];
        $clobber = $options['clobber'];
        $targetNS = $this->getTargetNamespaces($namespace);

        if ($this->getStatsContainer()->has($targetNS) && ($clobber === false)) {
            $this->addValueToExistingNamespace($targetNS, $value, $flatten);
        } elseif ($this->getStatsContainer()->has($targetNS) && ($clobber === true)) {
            $this->overwriteExistingNamespace($targetNS, $value, $flatten);
        } else {
            $this->addValueToNewNamespace($targetNS, $value, $flatten);
            $this->addPopulatedNamespace($targetNS);
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
        $this->getStatsContainer()->remove($targetNS);
        $this->removePopulatedNamespaces($targetNS);
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
        return $this->getStatsContainer()->get($targetNS);
    }

    /**
     * Check to see if value can be incremented.
     * Currently PHP allows numbers and strings to be incremented. We only want numbers
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isIncrementable($value)
    {
        return (new TypeHelper())->isIntOrFloat($value);
    }

    /**
     * Check to see if value can be negatively incremented. (forwards on to isIncrementable())
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isDecrementable($value)
    {
        return $this->isIncrementable($value);
    }

    /**
     * Check to see if stat namespace has start timer value
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function hasStartTimer($namespace)
    {
        return ($this->exists($namespace) &&
            is_array($this->getStat($namespace)) &&
            isset($this->getStat($namespace)['start']) &&
            is_numeric($this->getStat($namespace)['start'])
        );
    }

    /**
     * Check to see if stat namespace is a timer stat and has end timer value
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function hasEndTimer($namespace)
    {
        return ($this->hasStartTimer($namespace) &&
          isset($this->getStat($namespace)['end']));
    }

    /**
     * Check to see if stat namespace is a timer stat and has timer diff value
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function hasTimerDiff($namespace)
    {
        return ($this->hasStartTimer($namespace) &&
          $this->hasEndTimer($namespace) &&
          isset($this->getStat($namespace)['diff']));
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
     * Remove matching namespaces from the populated-namespaces array (typically when it becomes empty)
     *
     * @param $namespace
     *
     * @return bool
     */
    protected function removePopulatedNamespaces($namespace)
    {
        $targetNamespace = $namespace;
        $matches = array_filter($this->populatedNamespaces, function ($populatedNamespace) use ($targetNamespace) {
            if (preg_match("/^$targetNamespace(.*)/i", $populatedNamespace) === 1) {
                return true;
            }
            return false;
        });

        foreach (array_keys($matches) as $key) {
            unset($this->populatedNamespaces[$key]);
        }

        $this->sortPopulatedNamespaces();
        return true;
    }

    /**
     * @param  mixed $stats
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    protected function calculateStatsSum($stats)
    {
        $mathHelper = new MathHelper();

        if ($mathHelper->isSummable($stats)) {
            return $mathHelper->sum($stats);
        } else {
            throw new StatisticsCollectorException("Unable to return sum for supplied arguments (are the values numeric?)");
        }
    }

    /**
     * @param mixed $stats
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    protected function calculateStatsAverage($stats)
    {
        $mathHelper = new MathHelper();

        if ($mathHelper->isAverageable($stats)) {
            return $mathHelper->average($stats);
        } else {
            throw new StatisticsCollectorException("Unable to return average for supplied arguments (are the values numeric?)");
        }
    }

    /**
     * Sort populated namespaces first by namespace level and then alphabetical order
     *
     * @return bool
     */
    protected function sortPopulatedNamespaces()
    {
        usort($this->populatedNamespaces, function ($a, $b) {
            //sort on namespace nesting level
            $namespaceLevel = strnatcmp(substr_count($a, '.'), substr_count($b, '.'));
            if ($namespaceLevel === 0) {
                // if nesting level is equal (0), sort on alphabetical order using "natural order" algorithm
                return strnatcmp($a, $b);
            } else {
                return $namespaceLevel;
            }
        });
        return true;
    }

    /**
     * Get current stats container.
     *
     * Set $this->container to be an instance of \Dflydev\DotAccessData\Data (aliased as Container) if being
     * retrieved for the first time.
     *
     * @see Container
     */
    private function getStatsContainer()
    {
        if (!$this->container instanceof Container) {
            $this->container = new Container();
        }
        return $this->container;
    }

    /**
     * See AbstractCollector::addValueToNamespace() for documentation
     *
     * @see AbstractCollector::addValueToNamespace()
     *
     * @param $namespace
     * @param $value
     * @param $flatten
     */
    private function addValueToExistingNamespace($namespace, $value, $flatten)
    {
        if (is_array($value)) {
            $value = ($flatten === true) ? (new ArrayHelper())->flatten($value) : $value;
            $current = $this->getStatsContainer()->get($namespace);
            $new = (is_array($current)) ? array_merge_recursive($current, $value) : array_merge_recursive([$current],
              $value);
            $this->getStatsContainer()->set($namespace, $new);
        } else {
            $this->getStatsContainer()->append($namespace, $value);
        }
    }

    /**
     * See AbstractCollector::addValueToNamespace() for documentation
     *
     * @see AbstractCollector::addValueToNamespace()
     *
     * TODO:
     * Work out a better way to handle clobbering of compound stats. We can't just preserve unaffected data as it may
     * no longer be relevant.
     *
     * @param $namespace
     * @param $value
     * @param $flatten
     */
    private function overwriteExistingNamespace($namespace, $value, $flatten)
    {
        $typeHelper = new TypeHelper();
        if ($typeHelper->isCompoundStat($value)) {
            // FIXME:
            // when clobbering compound stats we *may* need to preserve other compound stat values which are not clobbered
            // so in this instance we just update the existing and overwrite the current values with the values
            // supplied in the $value argument.

            if ($flatten === true) {
                $newData = (new ArrayHelper())->flatten($value);
            } else {
                $newData = $value;
            }

            $current = $this->getStatsContainer()->get($namespace);
            if ($typeHelper->isCompoundStat($current)) {
                $updatedCompoundStat = array_replace($current, $newData);
            } else {
                $updatedCompoundStat = array_replace([$current], $newData);
            }

            $this->getStatsContainer()->set($namespace, $updatedCompoundStat);
        } else {
            // overwriting scalar stats requires the same behaviour as when adding a new value
            $this->addValueToNewNamespace($namespace, $value, $flatten);
        }
    }

    /**
     * See AbstractCollector::addValueToNamespace() for documentation
     *
     * @see AbstractCollector::addValueToNamespace()
     *
     * @param $namespace
     * @param $value
     * @param $flatten
     */
    private function addValueToNewNamespace($namespace, $value, $flatten)
    {
        if ($flatten === true && is_array($value)) {
            $flattenedValue = (new ArrayHelper())->flatten($value);
            $this->getStatsContainer()->set($namespace, $flattenedValue);
        } else {
            $this->getStatsContainer()->set($namespace, $value);
        }
    }

}