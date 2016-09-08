<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;

class RecoverSlotTest extends AbstractContentSlotTest
{
    protected $locationId = 43;
    protected $parentLocationId = 45;

    public function createSignal()
    {
        return new RecoverSignal(
            [
                'contentId' => $this->contentId,
                'newLocationId' => $this->locationId,
                'newParentLocationId' => $this->parentLocationId
            ]
        );
    }


    public function generateTags()
    {
        return [
            'content-'.$this->contentId,
            'relation-'.$this->contentId,
            'location-'.$this->parentLocationId,
            'parent-'.$this->parentLocationId,
        ];
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\RecoverSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal'];
    }
}
