<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Exception\DriverException;
use WebSocket\Exception\ExceptionInterface;

class ChromePage extends DevToolsConnection
{
    /**
     * @var array
     */
    private $pending_requests = [];

    /**
     * @var bool
     */
    private $page_ready = true;

    /**
     * @var bool
     */
    private $has_javascript_dialog = false;

    /**
     * @var string|null ID of the top-level frame.
     *
     * @see https://chromedevtools.github.io/devtools-protocol/#/Page.FrameId
     */
    private $main_frame_id;

    /**
     * @var array the top-level response.
     *
     * @see https://chromedevtools.github.io/devtools-protocol/tot/Network/#type-Response
     */
    private $response;

    /**
     * @var array
     */
    private $console_messages = [];

    /**
     * Connect and set up.
     *
     * @throws \Exception
     */
    public function connect($url = null): void
    {
        parent::connect();
        $this->send('Page.enable');
        $this->send('DOM.enable');
        $this->send('Network.enable');
        // @see https://gitlab.com/behat-chrome/chrome-mink-driver/-/merge_requests/253
        $this->send('Fetch.enable', [
            'handleAuthRequests' => true,
            'patterns' => [['urlPattern' => '*', 'resourceType' => 'Document']],
        ]);
        $this->send('Animation.setPlaybackRate', ['playbackRate' => 100000]);
        $this->send('Console.enable');
        $this->main_frame_id = $this->send('Page.getFrameTree')['frameTree']['frame']['id'] ?? null;
    }

    /**
     * Reset the latest response.
     */
    public function reset(): void
    {
        $this->response = null;
    }

    /**
     * Visit a new URL.
     *
     * @param $url
     * @throws ExceptionInterface
     * @throws DriverException
     */
    public function visit($url): void
    {
        if (count($this->pending_requests) > 0) {
            $this->waitFor(
                fn() => count($this->pending_requests) == 0
            );
        }
        $this->response = null;
        $this->page_ready = false;
        $this->send('Page.navigate', ['url' => $url]);
    }

    /**
     * Reload the current page.
     *
     * @throws \Exception
     */
    public function reload()
    {
        $this->response = null;
        $this->page_ready = false;
        $this->send('Page.reload');
    }

    /**
     * Wait for page to load.
     *
     * @throws DriverException
     */
    public function waitForLoad()
    {
        if (!$this->page_ready) {
            try {
                $this->waitFor(
                    fn() => $this->page_ready
                );
            } catch (ExceptionInterface) {
                throw new DriverException("Page not loaded");
            }
        }
    }

    /**
     * Get the response.
     *
     * @return array|null
     * @throws ExceptionInterface
     * @throws DriverException
     */
    public function getResponse()
    {
        $this->waitForHttpResponse();
        return $this->response;
    }

    public function hasJavascriptDialog(): bool
    {
        return $this->has_javascript_dialog;
    }

    /**
     * Get the browser's tabs.
     *
     * This method can return tabs outside of the driver's current browser
     * context, and thus should not be relied upon.
     *
     * @throws \Exception
     * @deprecated Deprecated in favor of ChromeBrowser::getTabs().
     */
    public function getTabs(): array
    {
        $tabs = [];
        foreach ($this->send('Target.getTargets')['targetInfos'] as $tab) {
            if ($tab['type'] == 'page') {
                $tabs[] = $tab;
            }
        }
        return array_reverse($tabs, true);
    }

    /**
     * Get all console messages since start or last clear.
     */
    public function getConsoleMessages(): array
    {
        return $this->console_messages;
    }

    /**
     * Clear the stored console messages.
     */
    public function clearConsoleMessages()
    {
        $this->console_messages = [];
    }

    /**
     * Wait for an HTTP response.
     *
     * @throws ExceptionInterface
     * @throws DriverException
     */
    private function waitForHttpResponse()
    {
        if (null === $this->response) {
            $parameters = ['expression' => 'document.readyState == "complete"'];
            $domReady = $this->send('Runtime.evaluate', $parameters)['result']['value'];
            if (count($this->pending_requests) == 0 && $domReady) {
                $this->response = [
                    'status' => 200,
                    'headers' => [],
                ];
                return;
            }

            $this->waitFor(
                fn() => null !== $this->response && count($this->pending_requests) == 0
            );
        }
    }

    /**
     * {inheritDoc}
     */
    protected function processResponse(array $data): bool
    {
        if (array_key_exists('method', $data)) {
            switch ($data['method']) {
                case 'Page.javascriptDialogOpening':
                    $this->has_javascript_dialog = true;
                    return true;
                case 'Page.javascriptDialogClosed':
                    $this->has_javascript_dialog = false;
                    break;
                case 'Fetch.requestPaused':
                    $this->sendWithoutWaiting('Fetch.continueRequest', ['requestId' => $data['params']['requestId']]);
                    break;
                case 'Fetch.authRequired':
                    $this->sendWithoutWaiting('Fetch.continueWithAuth', [
                        'requestId' => $data['params']['requestId'],
                        'authChallengeResponse' => ['response' => 'CancelAuth'],
                    ]);
                    break;
                case 'Network.requestWillBeSent':
                    if ($data['params']['type'] == 'Document') {
                        $this->pending_requests[$data['params']['requestId']] = true;
                    }
                    break;
                case 'Network.responseReceived':
                    if ($data['params']['type'] == 'Document') {
                        unset($this->pending_requests[$data['params']['requestId']]);
                        $frame_id = $data['params']['frameId'] ?? null;
                        if ($this->main_frame_id === null || $frame_id === $this->main_frame_id) {
                            $this->response = $data['params']['response'];
                        }
                    }
                    break;
                case 'Network.loadingFailed':
                    if ($data['params']['canceled']) {
                        unset($this->pending_requests[$data['params']['requestId']]);
                    }
                    break;
                case 'Page.loadEventFired':
                case 'Page.frameStartedLoading':
                    $this->page_ready = false;
                    break;
                case 'Page.navigatedWithinDocument':
                case 'Page.frameStoppedLoading':
                case 'Page.frameDetached':
                    $this->page_ready = true;
                    break;
                case 'Inspector.targetCrashed':
                    throw new DriverException('Browser crashed');
                case 'Console.messageAdded':
                    $this->console_messages[] = $data['params']['message'];
                    break;
                default:
                    break;
            }
        }

        return false;
    }
}
