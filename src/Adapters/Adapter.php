<?php

/**
 *  This file is part of reflar/webhooks.
 *
 *  Copyright (c) ReFlar.
 *
 *  https://reflar.redevs.org
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 */

namespace Reflar\Webhooks\Adapters;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Reflar\Webhooks\Models\Webhook;
use Reflar\Webhooks\Response;

abstract class Adapter
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;


    /**
     * @var \GuzzleHttp\Client
     */
    static $client;

    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * Set up the class
     */
    public function __construct() {
        $this->settings = app('flarum.settings');

        self::$client = new \GuzzleHttp\Client();
    }

    /**
     * @param Webhook $webhook
     * @param Response $response
     * @throws \ReflectionException
     */
    public function handle(Webhook $webhook, Response $response) {
        try {
            $this->send($webhook->url, $response);
            if (isset($webhook->error)) $webhook->setAttribute('error', null);
        } catch (GuzzleException $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $webhook->setAttribute(
                    'error',
                    (new \ReflectionClass($this->exception))->newInstance($e->getResponse(), $webhook->url)
                );
            } else {
                $webhook->setAttribute(
                    'error',
                    $e->getMessage()
                );
            }
        }

        $webhook->save();
    }

    /**
     * Sends a message through the webhook
     * @param string $url
     * @param Response $response
     * @throws RequestException
     */
    abstract function send(string $url, Response $response);

    /**
     * @param Response $response
     * @return array
     */
    abstract function toArray(Response $response);

    /**
     * @return bool
     */
    public function matchesFilters() {
        return true;
    }

    /**
     * @param string $url
     * @param array $json
     * @return \Psr\Http\Message\ResponseInterface
     * @throws RequestException
     */
    protected function request(string $url, array $json) {
        return self::$client->request('POST', $url, [
            'json' => $json,
        ]);
    }
}