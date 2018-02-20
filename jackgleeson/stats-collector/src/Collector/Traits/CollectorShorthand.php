<?php

namespace Statistics\Collector\Traits;

/**
 * Trait CollectorShorthand
 *
 * This Stats Collector behaviour provides access the standard Collector methods via a more succinct API.
 *
 * @package Statistics\Collector\Traits
 */
trait CollectorShorthand
{

    /**
     * Shorthand alias method for getting stats
     *
     * @see AbstractCollector::getStat()
     * @see AbstractCollector::getStats()
     *
     * @param string $namespace
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
     * Shorthand alias method for getting stats with keys
     *
     * @see AbstractCollector::getStat()
     * @see AbstractCollector::getStats()
     *
     * @param string|array $namespace
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
     * Shorthand alias method for adding stats
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
     * Shorthand alias method for overwriting stats
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
     * Shorthand alias method for removing stats
     *
     * @see AbstractCollector::removeStat()
     *
     * @param string $namespace
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function del($namespace)
    {
        return $this->removeStat($namespace);
    }

    /**
     * Shorthand alias method for incrementing a stat
     *
     * @see AbstractCollector::incrementStat()
     *
     * @param string $namespace
     * @param int|float $increment
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function inc($namespace, $increment = 1)
    {
        return $this->incrementStat($namespace, $increment);
    }

    /**
     * Shorthand alias method for incrementing a compound stat
     *
     * @see AbstractCollector::incrementCompoundStat()
     *
     * @param string $namespace
     * @param int|float|array $increment
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function incCpd($namespace, $increment = 1)
    {
        return $this->incrementCompoundStat($namespace, $increment);
    }

    /**
     * Shorthand alias method for decrementing stats
     *
     * @see AbstractCollector::decrementStat()
     *
     * @param string $namespace
     * @param int|float $decrement
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function dec($namespace, $decrement = -1)
    {
        return $this->decrementStat($namespace, $decrement);
    }

    /**
     * Shorthand alias method for decrementing a compound stat
     *
     * @see AbstractCollector::decrementCompoundStat()
     *
     * @param string $namespace
     * @param int|float|array $decrement
     *
     * @return \Statistics\Collector\AbstractCollector
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function decCpd($namespace, $decrement = 1)
    {
        return $this->decrementCompoundStat($namespace, $decrement);
    }

    /**
     * Shorthand alias method for recording a timestamp to serve as the start of a time period to be measured
     *
     * @see AbstractCollector::startTimer()
     *
     * @param string $namespace
     * @param mixed $customTimestamp
     * @param bool $useTimerNamespacePrefix
     *
     */
    public function start($namespace, $customTimestamp=null, $useTimerNamespacePrefix = true)
    {
        return $this->startTimer($namespace, $customTimestamp, $useTimerNamespacePrefix);
    }

    /**
     * Shorthand alias method for recording a timestamp to serve as the end of a time period and then
     * calculate the difference between the two timestamps recorded
     *
     * @see AbstractCollector::endTimer()
     *
     * @param string $namespace
     * @param mixed $customTimestamp
     * @param bool $useTimerNamespacePrefix
     *
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function end($namespace,  $customTimestamp=null, $useTimerNamespacePrefix = true)
    {
        return $this->endTimer($namespace, $customTimestamp, $useTimerNamespacePrefix);
    }

    /**
     * Shorthand alias method for returning the difference of a recorded start and end timestamp (including
     * microseconds)
     *
     * @see AbstractCollector::getTimerDiff()
     *
     * @param string $namespace
     * @param bool $useTimerNamespacePrefix
     *
     * @return float
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function diff($namespace, $useTimerNamespacePrefix = true)
    {
        return $this->getTimerDiff($namespace, $useTimerNamespacePrefix);
    }

    /**
     * Shorthand alias method for averaging stats
     *
     * @see AbstractCollector::getStatsAverage()
     * @see AbstractCollector::getStatAverage()
     *
     * @param string|array $namespace
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
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
     * Shorthand alias method for getting the sum of stats
     *
     * @see AbstractCollector::getStatsSum()
     * @see AbstractCollector::getStatSum()
     *
     * @param string|array $namespace
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
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
     * Shorthand alias method for counting the number of stats for a given namespace
     *
     * @see AbstractCollector::getStatsCount()
     * @see AbstractCollector::getStatCount()
     *
     * @param string|array $namespace
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
     * Shorthand alias method for returning all stats
     *
     * @see AbstractCollector::getAllStats()
     * @return array
     */
    public function all()
    {
        return $this->getAllStats();
    }

    /**
     * Shorthand alias method for setting/getting the current namespace
     *
     * @see AbstractCollector::setNamespace()
     * @see AbstractCollector::getCurrentNamespace()
     *
     * @param mixed $namespace optional
     *
     * @return string|\Statistics\Collector\AbstractCollector
     */
    public function ns($namespace = null)
    {
        if ($namespace === null) {
            return $this->getNamespace();
        } else {
            return $this->setNamespace($namespace);
        }
    }
}