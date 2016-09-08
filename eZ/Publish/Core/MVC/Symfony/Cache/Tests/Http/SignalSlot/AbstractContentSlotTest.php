<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

abstract class AbstractContentSlotTest extends AbstractSlotTest implements PurgeForContentExpectation
{
    protected static $contentId = 42;
    protected static $locationId = null;
    protected static $parentLocationId = null;

    /**
     * @return array
     */
    public static function generateTags()
    {
        $tags = [];
        if (static::$contentId){
            $tags = ['content-'.static::$contentId, 'relation-'.static::$contentId];
        }

        if (static::$locationId) {
            // self(s)
            $tags[] = 'location-'.static::$locationId;
            // children
            $tags[] = 'parent-'.static::$locationId;
        }

        if (static::$parentLocationId) {
            // parent(s)
            $tags[] = 'location-'.static::$parentLocationId;
            // siblings
            $tags[] = 'parent-'.static::$parentLocationId;
        }

        return $tags;
    }

    /**
     * @dataProvider getReceivedSignals
     */
    public function testReceivePurgesCacheForContent($signal)
    {
        $this->purgeClientMock->expects($this->once())->method('purgeByTags')->with(static::generateTags());
        $this->purgeClientMock->expects($this->never())->method('purgeAll');
        parent::receive($signal);
    }
}
