<?php

use Statistics\Collector\AbstractCollector;

class CiviCRMCollector extends AbstractCollector
{

    protected $defaultNamespace = "civi";

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