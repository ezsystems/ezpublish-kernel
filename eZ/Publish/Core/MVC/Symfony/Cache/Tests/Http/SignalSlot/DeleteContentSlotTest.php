<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal;

class DeleteContentSlotTest extends AbstractContentSlotTest
{
    public function createSignal()
    {
        return new DeleteContentSignal(['contentId' => $this->contentId, 'affectedLocationIds' => [45, 55]]);
    }


    public function generateTags()
    {
        $tags = parent::generateTags();
        $tags[] = 'path-45';
        $tags[] = 'path-55';

        return $tags;
    }

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\DeleteContentSlot';
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal'];
    }
}
