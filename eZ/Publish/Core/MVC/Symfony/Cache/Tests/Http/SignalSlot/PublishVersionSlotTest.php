<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use eZ\Publish\SPI\Persistence\Content\Location;

class PublishVersionSlotTest extends AbstractContentSlotTest
{
    protected $locationId = 45;
    protected $parentLocationId = 32;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit_Framework_MockObject_MockObject */
    protected $spiLocationHandlerMock;

    public function getSlotClass()
    {
        return 'eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\PublishVersionSlot';
    }

    public function createSignal()
    {
        return new PublishVersionSignal(['contentId' => $this->contentId]);
    }

    public function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal'];
    }

    protected function createSlot()
    {
        $class = $this->getSlotClass();
        if ($this->spiLocationHandlerMock === null) {
            $this->spiLocationHandlerMock = $this->getMock('eZ\Publish\SPI\Persistence\Content\Location\Handler');
        }

        return new $class($this->purgeClientMock, $this->spiLocationHandlerMock);
    }

    /**
     * @dataProvider getUnreceivedSignals
     */
    public function testDoesNotReceiveOtherSignals($signal)
    {
        $this->purgeClientMock->expects($this->never())->method('purge');
        $this->purgeClientMock->expects($this->never())->method('purgeAll');

        $this->spiLocationHandlerMock->expects($this->never())->method('loadLocationsByContent');

        $this->slot->receive($signal);
    }

    /**
     * @dataProvider getReceivedSignals
     */
    public function testReceivePurgesCacheForTags($signal)
    {
        $this->spiLocationHandlerMock
            ->expects($this->once())
            ->method('loadLocationsByContent')
            ->with($this->contentId)
            ->willReturn(
                [
                    new Location(['id' => $this->locationId, 'parentId' => $this->parentLocationId]),
                ]
            );

        $this->purgeClientMock->expects($this->once())->method('purge')->with($this->generateTags());
        $this->purgeClientMock->expects($this->never())->method('purgeAll');
        parent::receive($signal);
    }
}
