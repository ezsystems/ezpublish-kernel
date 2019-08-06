<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\EventSubscriber;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Events\User\DeleteUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteUserSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeleteUserEvent::class => 'onDeleteUser',
        ];
    }

    public function onDeleteUser(DeleteUserEvent $event): void
    {
        $this->contentTypeService->deleteUserDrafts($event->getUser()->id);
    }
}
