<?php

use Statistics\Collector\Collector;
/**
 * Test Collector (extends Collector)
 */
class TestCollector extends Collector
{

    protected $defaultNamespace = "test";

    protected function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

}