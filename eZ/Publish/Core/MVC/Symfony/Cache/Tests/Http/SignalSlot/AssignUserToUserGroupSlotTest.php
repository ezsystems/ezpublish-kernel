<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal;

class AssignUserToUserGroupSlotTest extends AbstractContentSlotTest
{
    public function createSignal()
    {
        return new AssignUserToUserGroupSignal(['userId' => $this->contentId, 'userGroupId' => 99]);
    }

    public function generateTags()
    {
        return ['content-'.$this->contentId, 'content-99'];
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\AssignUserToUserGroupSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal'];
    }
}