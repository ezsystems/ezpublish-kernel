<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\AssignSectionSlot;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\CreateSectionSignal;
use PHPUnit_Framework_TestCase;

class AssignSectionSlotTest extends PHPUnit_Framework_TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\AssignSectionSlot */
    private $slot;

    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger|\PHPUnit_Framework_MockObject_MockObject */
    private $cachePurgerMock;

    private $contentId = 42;

    public function setUp()
    {
        $this->cachePurgerMock = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger' );
        $this->slot = new AssignSectionSlot( $this->cachePurgerMock );
    }

    public function testDoesNotReceiveOtherSignals()
    {
        $this->cachePurgerMock->expects( $this->never() )->method( 'purgeForContent' );
        $this->cachePurgerMock->expects( $this->never() )->method( 'purgeAll' );

        $this->slot->receive( new CreateSectionSignal() );
    }

    public function testReceiveClearsContentCache()
    {
        $this->cachePurgerMock->expects( $this->once() )->method( 'purgeForContent' )->with( $this->contentId );
        $this->cachePurgerMock->expects( $this->never() )->method( 'purgeAll' );

        $this->slot->receive( $this->createSignal() );
    }

    protected function createSignal()
    {
        return new AssignSectionSignal( ['contentId' => $this->contentId] );
    }
}
