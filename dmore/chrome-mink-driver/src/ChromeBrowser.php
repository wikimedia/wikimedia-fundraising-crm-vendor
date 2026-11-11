<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Exception\DriverException;
use WebSocket\Exception\ExceptionInterface;

class ChromeBrowser extends DevToolsConnection
{
    /**
     * The ID of the current BrowserContext.
     */
    private ?string $context_id = null;

    /**
     * @var bool
     */
    private $headless = true;

    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * @var string
     */
    private $http_uri;

    /**
     * @var string
     */
    private $version;

    /**
     * Set the HTTP client.
     */
    public function setHttpClient(HttpClient $client): void
    {
        $this->http_client = $client;
    }

    /**
     * Set the HTTP URI.
     *
     * @param string $http_uri
     */
    public function setHttpUri($http_uri): void
    {
        $this->http_uri = $http_uri;
    }

    /**
     * Start.
     *
     * @throws DriverException
     */
    public function start(): string
    {
        $response = $this->http_client->get($this->http_uri . '/json/version');
        $versionInfo = json_decode($response);

        if (null === $versionInfo) {
            throw new \RuntimeException(
                sprintf(
                    "Could not fetch version information from %s. Check if Chrome is running. " .
                    "See docs/troubleshooting.md if Chrome crashed unexpectedly." . PHP_EOL .
                    "Json Error: %s." . PHP_EOL .
                    "Response was: %s",
                    $this->http_uri . '/json/version',
                    json_last_error_msg(),
                    $response
                )
            );
        }

        // Detect Browser version
        if (property_exists($versionInfo, 'Browser')) {
            $start = strpos($versionInfo->Browser, '/') + 1;
            $this->version = (int) substr($versionInfo->Browser, $start, strpos($versionInfo->Browser, '.') - $start);
        }

        // Isolate the driver's windows into their own browser context. This works the same way whether Chrome
        // is running headless or not, and keeps the driver's windows separate from any other windows Chrome may
        // have open (e.g. first-run dialogs, or windows opened outside of the driver, when running headed).
        //
        // Whether this succeeds also doubles as our headless-mode signal, since neither the Browser nor
        // User-Agent version strings reliably indicate it (e.g. headless-shell's strings are indistinguishable
        // from headed Chrome's). This isn't perfect either - headed Chrome can support isolated contexts too -
        // but it's the best signal available without a dedicated CDP method to ask Chrome directly.
        try {
            $this->context_id = $this->send('Target.createBrowserContext')['browserContextId'];
            $data = $this->send(
                'Target.createTarget',
                ['url' => 'about:blank', 'browserContextId' => $this->context_id]
            );
            $this->headless = true;
            return $data['targetId'];
        } catch (DriverException $exception) {
            // -32601: Method not found (e.g., Target.createBrowserContext not available)
            // -32000: Browser context not supported (e.g., headed mode with limited DevTools)
            if ($exception->getCode() !== -32601 && $exception->getCode() !== -32000) {
                throw $exception;
            }
            $this->headless = false;
            // Isolated browser contexts are unavailable in this Chrome instance; fall back to the default context.
            $this->context_id = null;
        }

        // Fall back to non-headless method (/json/new endpoint)
        $json = $this->http_client->put($this->http_uri . '/json/new');
        $response = json_decode($json, true);
        return $response['id'];
    }

    /**
     * Close the session.
     *
     * @throws ExceptionInterface
     */
    public function close(): void
    {
        if (null !== $this->context_id) {
            $this->send('Target.disposeBrowserContext', ['browserContextId' => $this->context_id]);
        }
        parent::close();
    }

    /**
     * {inheritDoc}
     */
    protected function processResponse(array $data): bool
    {
        return false;
    }


    /**
     * @return bool
     */
    public function isHeadless()
    {
        return $this->headless;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the browser's tabs.
     */
    public function getTabs(): array
    {
        $tabs = [];
        foreach ($this->send('Target.getTargets')['targetInfos'] as $tab) {
            // Return targets of type page that are within this browser's
            // context (if browser context ID is set here and on the tab).
            $browser_context_set = isset($this->context_id) && isset($tab['browserContextId']);
            $wrong_browser_context = $browser_context_set && $tab['browserContextId'] !== $this->context_id;
            if ($tab['type'] === 'page' && !$wrong_browser_context) {
                $tabs[] = $tab;
            }
        }
        return array_reverse($tabs, true);
    }
}
