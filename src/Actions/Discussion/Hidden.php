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

namespace Reflar\Webhooks\Actions\Discussion;


use Reflar\Webhooks\Action;
use Reflar\Webhooks\Response;

class Hidden extends Action
{

    /**
     * @param \Flarum\Discussion\Event\Hidden $event
     * @return Response
     */
    function listen($event)
    {
        return Response::build()
            ->setTitle(
                $this->translate('discussion.hidden', $event->discussion->title)
            )
            ->setURL('discussion', [
                'id' => $event->discussion->id
            ])
            ->setDescription($event->discussion->startPost->content)
            ->setAuthor($event->actor)
            ->setColor('fed330')
            ->setTimestamp($event->discussion->hide_time);
    }
}