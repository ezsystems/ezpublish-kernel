<?php
/**
 * File containing the ContentPreviewHelperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper\Tests;

use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;

class ContentPreviewHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->eventDispatcher = $this->getMock( 'Symfony\Component\EventDispatcher\EventDispatcherInterface' );
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }

    public function testChangeConfigScope()
    {
        $newSiteAccessName = 'test';
        $newSiteAccess = new SiteAccess( $newSiteAccessName, 'preview' );
        $event = new ScopeChangeEvent( $newSiteAccess );
        $this->eventDispatcher
            ->expects( $this->once() )
            ->method( 'dispatch' )
            ->with( MVCEvents::CONFIG_SCOPE_CHANGE, $this->equalTo( $event ) );

        $originalSiteAccess = new SiteAccess( 'foo', 'bar' );
        $helper = new ContentPreviewHelper(
            $this->eventDispatcher,
            $this->configResolver
        );
        $helper->setSiteAccess( $originalSiteAccess );
        $this->assertEquals(
            $newSiteAccess,
            $helper->changeConfigScope( $newSiteAccessName )
        );
    }

    public function testRestoreConfigScope()
    {
        $originalSiteAccess = new SiteAccess( 'foo', 'bar' );
        $event = new ScopeChangeEvent( $originalSiteAccess );
        $this->eventDispatcher
            ->expects( $this->once() )
            ->method( 'dispatch' )
            ->with( MVCEvents::CONFIG_SCOPE_RESTORE, $this->equalTo( $event ) );

        $helper = new ContentPreviewHelper(
            $this->eventDispatcher,
            $this->configResolver
        );
        $helper->setSiteAccess( $originalSiteAccess );
        $this->assertEquals(
            $originalSiteAccess,
            $helper->restoreConfigScope()
        );
    }
}
