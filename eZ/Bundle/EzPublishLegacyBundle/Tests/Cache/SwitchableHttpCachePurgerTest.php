<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Cache;

use eZ\Bundle\EzPublishLegacyBundle\Cache\SwitchableHttpCachePurger;
use PHPUnit_Framework_TestCase;

class SwitchableHttpCachePurgerTest extends PHPUnit_Framework_TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger|\PHPUnit_Framework_MockObject_MockObject */
    private $gatewayCachePurgerMock;

    /** @var \eZ\Bundle\EzPublishLegacyBundle\Cache\SwitchableHttpCachePurger */
    private $httpCachePurger;

    public function setUp()
    {
        $this->gatewayCachePurgerMock = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger' );
        $this->httpCachePurger = new SwitchableHttpCachePurger( $this->gatewayCachePurgerMock );
    }

    public function testPurgeSwitchedOn()
    {
        $this->httpCachePurger->switchOn();

        $this->gatewayCachePurgerMock->expects( $this->once() )->method( 'purge' )->willReturn( $this->getCacheElements() );
        self::assertEquals(
            $this->getCacheElements(),
            $this->httpCachePurger->purge( $this->getCacheElements() )
        );
    }

    public function testPurgeSwitchedOff()
    {
        $this->httpCachePurger->switchOff();
        $this->gatewayCachePurgerMock->expects( $this->never() )->method( 'purge' );
        self::assertEquals(
            $this->getCacheElements(),
            $this->httpCachePurger->purge( $this->getCacheElements() )
        );
    }

    public function testPurgeAllSwitchedOn()
    {
        $this->httpCachePurger->switchOn();
        $this->gatewayCachePurgerMock->expects( $this->once() )->method( 'purgeAll' );
        $this->httpCachePurger->purgeAll();
    }

    public function testPurgeAllSwitchedOff()
    {
        $this->httpCachePurger->switchOff();
        $this->gatewayCachePurgerMock->expects( $this->never() )->method( 'purgeAll' );
        $this->httpCachePurger->purgeAll();
    }

    private function getCacheElements()
    {
        return array( 1, 2, 3 );
    }
}
