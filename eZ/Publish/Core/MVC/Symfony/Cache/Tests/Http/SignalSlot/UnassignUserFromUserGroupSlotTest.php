<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal;
use PHPUnit_Framework_MockObject_Builder_InvocationMocker;

class UnassignUserFromUserGroupSlotTest extends AbstractPurgeAllSlotTest implements SlotTest
{
    public static function createSignal()
    {
        return new UnAssignUserFromUserGroupSignal();
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\UnassignUserFromUserGroupSlot';
    }

    public static function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal'];
    }
}
