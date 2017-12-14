<?php

namespace Statistics\Collector;

interface iCollectorVerbose
{

    public static function getInstance();

    public function addStat($name, $value, $options = []);

    public function removeStat($namespace);

    public function getStat($namespace, $withKeys = null, $default = null);

    public function getStats(array $namespaces, $withKeys = null, $default = null);

    public function getStatCount($namespace);

    public function getStatsCount(array $namespace);

    public function getStatAverage($namespace);

    public function getStatsAverage(array $namespace);

    public function getStatSum($namespace);

    public function getStatsSum(array $namespace);

    public function incrementStat($namespace, $increment);

    public function decrementStat($namespace, $decrement);

    public function getAllStats();

    public function setNamespace($namespace);

    public function getCurrentNamespace();

}