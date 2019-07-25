<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Slot\User;

use eZ\Bundle\EzPublishCoreBundle\EventListener\User\eZ;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;

class DeleteUser extends Slot
{
    /** @var eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\UserService\DeleteUserSignal) {
            return;
        }

        $this->contentTypeService->deleteUserDrafts($signal->userId);
    }
}
