<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

abstract class AbstractPurgeForContentSlotTest extends AbstractSlotTest implements PurgeForContentExpectation
{
    protected static $contentId = 42;
    protected static $locationIds = [];

    /**
     * @return mixed
     */
    public static function getContentId()
    {
        return static::$contentId;
    }

    /**
     * @return mixed[]
     */
    public static function getLocationIds()
    {
        return static::$locationIds;
    }

    /**
     * @dataProvider getReceivedSignals
     */
    public function testReceivePurgesCacheForContent($signal)
    {
        $this->cachePurgerMock->expects($this->once())->method('purgeForContent')->with(self::getContentId(), self::getLocationIds());
        $this->cachePurgerMock->expects($this->never())->method('purgeAll');
        parent::receive($signal);
    }
}
