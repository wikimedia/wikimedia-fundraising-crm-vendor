<?php

namespace Statistics\Collector;

/**
 * Statistics Collector
 *
 * This utility is designed to allow the simple namespace structured key/value
 * storage of statistics during the lifecycle of any process.
 *
 * Stats are stored in defined namespaces. The namespace
 * structure/convention/naming is entirely up to the implementation e.g.
 * queue.failed.transactions, civi.users.subscribed, server1.website.clicks are all acceptable
 */
class Collector extends AbstractCollector
{

    protected $defaultNamespace = "root";

    /**
     * Return the default namespace to be used if a custom namespace is not set.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

}