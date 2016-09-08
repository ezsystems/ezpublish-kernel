<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal;

class DeleteLocationSlotTest extends AbstractContentSlotTest
{
    protected $locationId = 45;
    protected $parentLocationId = 43;

    public function createSignal()
    {
        return new DeleteLocationSignal(
            [
                'contentId' => $this->contentId,
                'locationId' => $this->locationId,
                'parentLocationId' => $this->parentLocationId
            ]
        );
    }

    public function generateTags()
    {
        $tags = parent::generateTags();
        $tags[] = 'path-'.$this->locationId;

        return $tags;
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteLocationSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal'];
    }
}
