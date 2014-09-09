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
    private $contentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

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
        $this->contentService = $this->getMock( 'eZ\Publish\API\Repository\ContentService' );
        $this->locationService = $this->getMock( 'eZ\Publish\API\Repository\LocationService' );
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
            $this->contentService,
            $this->locationService,
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
            $this->contentService,
            $this->locationService,
            $this->eventDispatcher,
            $this->configResolver
        );
        $helper->setSiteAccess( $originalSiteAccess );
        $this->assertEquals(
            $originalSiteAccess,
            $helper->restoreConfigScope()
        );
    }

    public function testGetPreviewLocationNoMainLocation()
    {
        $contentId = 123;
        $rootLocationId = 456;
        $contentInfo = $this
            ->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' )
            ->setConstructorArgs( array( array( 'id' => $contentId ) ) )
            ->getMockForAbstractClass();
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $contentId )
            ->will( $this->returnValue( $contentInfo ) );
        $this->locationService
            ->expects( $this->never() )
            ->method( 'loadLocation' );
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'content.tree_root.location_id' )
            ->will( $this->returnValue( $rootLocationId ) );

        $helper = new ContentPreviewHelper(
            $this->contentService,
            $this->locationService,
            $this->eventDispatcher,
            $this->configResolver
        );
        $location = $helper->getPreviewLocation( $contentId );
        $this->assertInstanceOf( 'eZ\Publish\API\Repository\Values\Content\Location', $location );
        $this->assertSame( $contentInfo, $location->contentInfo );
        $this->assertSame( $rootLocationId, $location->id );
    }

    public function testGetPreviewLocation()
    {
        $contentId = 123;
        $locationId = 456;
        $contentInfo = $this
            ->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' )
            ->setConstructorArgs( array( array( 'id' => $contentId, 'mainLocationId' => $locationId ) ) )
            ->getMockForAbstractClass();
        $location = $this
            ->getMockBuilder( 'eZ\Publish\Core\Repository\Values\Content\Location' )
            ->setConstructorArgs( array( array( 'id' => $locationId, 'contentInfo' => $contentInfo ) ) )
            ->getMockForAbstractClass();
        $this->contentService
            ->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $contentId )
            ->will( $this->returnValue( $contentInfo ) );
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $locationId )
            ->will( $this->returnValue( $location ) );

        $helper = new ContentPreviewHelper(
            $this->contentService,
            $this->locationService,
            $this->eventDispatcher,
            $this->configResolver
        );
        $returnedLocation = $helper->getPreviewLocation( $contentId );
        $this->assertSame( $location, $returnedLocation );
        $this->assertSame( $contentInfo, $returnedLocation->contentInfo );
    }
}
