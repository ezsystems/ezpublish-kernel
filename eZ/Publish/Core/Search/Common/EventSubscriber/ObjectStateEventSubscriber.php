<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\EventSubscriber;

use eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ObjectStateEventSubscriber extends AbstractSearchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SetContentStateEvent::class => 'onSetContentState',
        ];
    }

    public function onSetContentState(SetContentStateEvent $event)
    {
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($event->getContentInfo()->id);

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $contentInfo->id,
                $contentInfo->currentVersionNo
            )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentInfo->id);
        foreach ($locations as $location) {
            $this->searchHandler->indexLocation($location);
        }
    }
}
