<?php

namespace DMore\ChromeDriver;

use Behat\Mink\Exception\DriverException;
use WebSocket\Client;
use WebSocket\Configuration;
use WebSocket\Exception\ConnectionLevelInterface;
use WebSocket\Exception\ExceptionInterface;
use WebSocket\Message\Message;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

abstract class DevToolsConnection
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $command_id = 1;

    /**
     * @var int|float|null
     */
    private $socket_timeout;

    /**
     * @param string $url
     * @param int|float|null $socket_timeout
     *
     * @throws \InvalidArgumentException If socket_timeout is not null or a positive number
     */
    public function __construct(private $url, $socket_timeout = null)
    {
        if ($socket_timeout !== null && (!is_numeric($socket_timeout) || $socket_timeout <= 0)) {
            throw new \InvalidArgumentException('socket_timeout must be a positive number or null');
        }
        $this->socket_timeout = $socket_timeout === null ? null : (float) $socket_timeout;
    }

    /**
     * Check DevTools connection.
     *
     * @return bool
     * @deprecated since 2.8.0 this method has always returned false and is not considered useful
     */
    public function canDevToolsConnectionBeEstablished()
    {
        // Since 2.8.0 this has always returned false, as the `url` injected into `ChromePage` is the *websocket url*
        // and not the base url of the JSON API - so adding `/json/version` to the URL has always resulted in a 404.
        //
        // The method was only added as an attempt to detect whether Chrome itself was healthy if the driver received
        // a timeout when reading from the websocket, in order to decide whether to retry the read. However, timeouts
        // are much more commonly just because Chrome has nothing to report (e.g. a pageload or client-side event is
        // taking longer than the socket read timeout to complete), or a tab has crashed. In both these cases, we have
        // observed that the JSON API of the main browser process is always available, so this method of check never
        // identifies new genuine failure cases but historically was producing false positive reports that the browser
        // had crashed.
        return false;
    }

    /**
     * Get the current URL.
     */
    protected function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Connect to the client.
     */
    public function connect($url = null): void
    {
        $configuration = new Configuration(
            frameSize: 2000000, // Chrome closes the connection if a message is sent in fragments
            timeout: $this->socket_timeout,
        );

        $this->client = new Client($url ?? $this->url, $configuration);

        $this->client
            ->addMiddleware(new CloseHandler())
            ->addMiddleware(new PingResponder());
    }

    /**
     * Close the client connection.
     */
    public function close(): void
    {
        $this->client->close();
    }

    /**
     * Send a command to the client, return result.
     *
     * @param string $command
     * @return null|string|string[][]
     * @throws \Exception
     */
    public function send($command, array $parameters = []): array
    {
        $payload['id'] = $this->command_id++;
        $payload['method'] = $command;
        if (!empty($parameters)) {
            $payload['params'] = $parameters;
        }

        $this->client->text(json_encode($payload));

        $data = $this->waitFor(
            fn($data) => array_key_exists('id', $data) && $data['id'] == $payload['id']
        );

        return $data['result'] ?? ['result' => ['type' => 'undefined']];
    }

    /**
     * Send a command to the client, ignore result.
     *
     * Waiting for a reply in processResponse() could consume messages an
     * outer waitFor() is expecting. Eventual reply is ignored.
     *
     * @throws \Exception
     */
    protected function sendWithoutWaiting(string $command, array $parameters = []): void
    {
        $payload = ['id' => $this->command_id++, 'method' => $command];
        if (!empty($parameters)) {
            $payload['params'] = $parameters;
        }

        $this->client->text(json_encode($payload));
    }

    /**
     * Wait on response from client.
     *
     * @return mixed|null
     * @throws ExceptionInterface
     * @throws DriverException
     */
    protected function waitFor(callable $is_ready)
    {
        $data = [];
        while (true) {
            try {
                $message = $this->client->receive();
                $response = $message->getContent();
            } catch (ConnectionLevelInterface $exception) {
                // NB - this may throw a ConnectionTimeoutException if the socket read times out when Chrome has nothing
                // to report within the specified socket_timeout - e.g. initial server-side document request takes
                // longer than the timeout, or Chrome is stalled on a window.alert|confirm|prompt or other client-side
                // operation. This catch is retained temporarily as this will be the correct place to handle a Timeout
                // in future.
                throw $exception;
            }

            if (is_null($response)) {
                // There seems to be no valid case where we could actually get an explicit `null` value from the
                // underlying websocket, other than perhaps the socket being closed by Chrome. Certainly, calling code
                // (which does not generally check the response) should not blindly continue at this point.
                throw new DriverException(
                    'Received unexpected NULL payload from Chrome websocket'
                );
            }

            if ($data = json_decode($response, true)) {
                if (array_key_exists('error', $data)) {
                    $message = isset($data['error']['data']) ?
                    $data['error']['message'] . '. ' . $data['error']['data'] : $data['error']['message'];
                    throw new DriverException($message, $data['error']['code']);
                }

                // What's this doing?
                if ($this->processResponse($data)) {
                    break;
                }

                if ($is_ready($data)) {
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Process a client response.
     *
     * @throws DriverException
     */
    abstract protected function processResponse(array $data): bool;
}
