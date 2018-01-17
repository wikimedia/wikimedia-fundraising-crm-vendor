<?php

namespace Statistics\Collector\Traits;

/**
 * Trait SingletonInheritance
 *
 * This behaviour provides a classic Singleton PHP pattern implementation with some enhancements to support
 * inheritance and unit testing.
 *
 * For information on why the standard implementation of Singleton in PHP does not support inheritance, read here:
 * https://github.com/jackgleeson/singleton-inheritance-test-php
 *
 * @package Statistics\Collector\Traits
 */
trait SingletonInheritance
{

    /**
     * Singleton instances container
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * @return \Statistics\Collector\AbstractCollector
     */
    public static function getInstance()
    {
        $class = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
        }
        return self::$instances[$class];
    }

    /**
     * Empty singleton instances.
     * This method is workaround to add singleton testability as explained here
     * https://gonzalo123.com/2012/09/24/the-reason-why-singleton-is-a-problem-with-phpunit/
     *
     * @param bool $all
     *
     * @return bool
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
     * Add some Singleton visibility restrictions to avoid inconsistencies.
     */

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function __wakeup()
    {
        return [];
    }

}