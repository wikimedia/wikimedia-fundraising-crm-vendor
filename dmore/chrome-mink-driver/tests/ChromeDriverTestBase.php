<?php

namespace DMore\ChromeDriverTests;

use DMore\ChromeDriver\ChromeDriver;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * Note that the majority of driver test coverage is provided via minkphp/driver-testsuite.
 *
 * Consider building on coverage there first!
 */
class ChromeDriverTestBase extends TestCase
{
    /**
     * @var ChromeDriver
     */
    protected $driver;

    /**
     * {inheritDoc}
     */
    protected function setUp(): void
    {
        $this->driver = $this->getDriver();
    }

    /**
     * {inheritDoc}
     */
    protected function tearDown(): void
    {
        if ($this->driver->isStarted()) {
            $this->driver->stop();
        }
        // Release reference to driver to allow garbage collection
        $this->driver = null;
        parent::tearDown();
    }

    /**
     * @return ChromeDriver
     */
    private function getDriver(): ChromeDriver
    {
        $options = [
            'domWaitTimeout' => ChromeDriver::$domWaitTimeoutDefault,
            'socketTimeout' => ChromeDriver::$socketTimeoutDefault,
        ];
        return new ChromeDriver('http://localhost:9222', null, 'about:blank', $options);
    }

    /**
     * Helper function to open a page with arbitrary content using a data url
     *
     * Note that pages opened like this will have an anonymous origin for CORS so may not be able to perform some
     * remote / privileged operations through JS.
     *
     * @param string $html
     *
     * @return void
     * @throws \Behat\Mink\Exception\DriverException
     */
    protected function visitPageWithContent(string $html): void
    {
        $this->driver->visit('data:text/html;charset=utf-8;base64,' . base64_encode($html));
    }
}
