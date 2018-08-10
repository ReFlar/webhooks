<?php

/**
 *  This file is part of reflar/webhooks
 *
 *  Copyright (c) ReFlar.
 *
 *  https://reflar.redevs.org
 *
 *  For the full copyright and license information, please view the license.md
 *  file that was distributed with this source code.
 */

namespace Reflar\Webhooks\Command;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\Exception\PermissionDeniedException;
use Reflar\Webhooks\Command\CreateWebhook;
use Reflar\Webhooks\Models\Webhook;
use Reflar\Webhooks\Validator\WebhookValidator;

class CreateWebhookHandler
{
    use AssertPermissionTrait;

    /**
     * @var WebhookValidator
     */
    protected $validator;

    /**
     * @param WebhookValidator $validator
     */
    public function __construct(WebhookValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CreateWebhook $command
     *
     * @throws PermissionDeniedException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Webhook
     */
    public function handle(CreateWebhook $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $this->assertAdmin($actor);

        $webhook = Webhook::build(
            array_get($data, 'service'),
            array_get($data, 'url')
        );

        $this->validator->assertValid($webhook->getAttributes());

        $webhook->save();

        return $webhook;
    }


}
