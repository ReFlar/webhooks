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

namespace Reflar\Webhooks\Adapters\Discord;


use Flarum\Http\UrlGenerator;
use Reflar\Webhooks\Response;

class Adapter extends \Reflar\Webhooks\Adapters\Adapter
{
    public static $client;

    protected $exception =  DiscordException::class;

    /**
     * Sends a message through the webhook
     * @param string $url
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function send(string $url, Response $response) {
        if (!isset($response)) return;

        return $this->request($url, [
            "username" => $this->settings->get('reflar-webhooks.settings.discordName') ?: $this->settings->get('forum_title'),
            "avatar_url" => $this->getAvatarUrl(),
            "embeds" => [
                $this->toArray($response)
            ]
        ]);

//        $http_status = $response ?: $response->getStatusCode();

//        if ($http_status != 200 && $http_status >= 400) {
//            app('log')->error("[reflar/webhooks] Discord: An error may have occurred:  HTTP " . $http_status);
//            throw new DiscordException($http_status, $response->getStatusCode(), $response->getReasonPhrase());
//        }
    }

    /**
     * @return null|string
     */
    protected function getAvatarUrl() {
        $faviconPath = $this->settings->get('favicon_path');
        $logoPath = $this->settings->get('logo_path');

        return ($faviconPath ?: $logoPath) ? app(UrlGenerator::class)->to('forum')->path('assets/' . ($faviconPath ?: $logoPath)) : null;
    }

    /**
     * @param Response $response
     * @return array
     */
    function toArray(Response $response)
    {
        return [
            'title' => $response->title,
            'url' => $response->url,
            'description' => $response->description,
            'author' => isset($response->author) ? [
                'name' => $response->author->username,
                'url' => $response->getAuthorUrl(),
                'icon_url' => $response->author->avatar_url,
            ] : null,
            'color' => $response->getColor(),
            'timestamp' => isset($response->timestamp) ? $response->timestamp : date("c"),
            'type' => 'rich'
        ];
    }
}