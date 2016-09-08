<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;

class MoveSubtreeSlotTest extends AbstractContentSlotTest
{
    protected $locationId = 45;
    protected $parentLocationId = 43;

    public function createSignal()
    {
        return new MoveSubtreeSignal(
            [
                'locationId' => $this->locationId,
                'newParentLocationId' => $this->parentLocationId
            ]
        );
    }

    public function generateTags()
    {
        return ['path-'.$this->locationId, 'location-'.$this->parentLocationId, 'parent-'.$this->parentLocationId];
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\MoveSubtreeSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal'];
    }
}
