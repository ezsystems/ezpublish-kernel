<?php

/**
 * File containing the ContentPreviewHelperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    private $siteAccessRouter;

    /**
     * @var \eZ\Publish\Core\Helper\ContentPreviewHelper
     */
    private $previewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->siteAccessRouter = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface');
        $this->previewHelper = new ContentPreviewHelper($this->eventDispatcher, $this->siteAccessRouter);
    }

    public function testChangeConfigScope()
    {
        $newSiteAccessName = 'test';
        $newSiteAccess = new SiteAccess($newSiteAccessName);

        $this->siteAccessRouter
            ->expects($this->once())
            ->method('matchByName')
            ->with($this->equalTo($newSiteAccessName))
            ->willReturn($newSiteAccess);

        $event = new ScopeChangeEvent($newSiteAccess);
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::CONFIG_SCOPE_CHANGE, $this->equalTo($event));

        $originalSiteAccess = new SiteAccess('foo', 'bar');
        $this->previewHelper->setSiteAccess($originalSiteAccess);
        $this->assertEquals(
            $newSiteAccess,
            $this->previewHelper->changeConfigScope($newSiteAccessName)
        );
    }

    public function testRestoreConfigScope()
    {
        $originalSiteAccess = new SiteAccess('foo', 'bar');
        $event = new ScopeChangeEvent($originalSiteAccess);
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::CONFIG_SCOPE_RESTORE, $this->equalTo($event));

        $this->previewHelper->setSiteAccess($originalSiteAccess);
        $this->assertEquals(
            $originalSiteAccess,
            $this->previewHelper->restoreConfigScope()
        );
    }

    public function testPreviewActive()
    {
        $this->assertFalse($this->previewHelper->isPreviewActive());
        $this->previewHelper->setPreviewActive(true);
        $this->assertTrue($this->previewHelper->isPreviewActive());
        $this->previewHelper->setPreviewActive(false);
        $this->assertFalse($this->previewHelper->isPreviewActive());
    }

    public function testPreviewedContent()
    {
        $this->assertNull($this->previewHelper->getPreviewedContent());
        $content = $this->getMock('\eZ\Publish\API\Repository\Values\Content\Content');
        $this->previewHelper->setPreviewedContent($content);
        $this->assertSame($content, $this->previewHelper->getPreviewedContent());
    }

    public function testPreviewedLocation()
    {
        $this->assertNull($this->previewHelper->getPreviewedLocation());
        $location = $this->getMock('\eZ\Publish\API\Repository\Values\Content\Location');
        $this->previewHelper->setPreviewedLocation($location);
        $this->assertSame($location, $this->previewHelper->getPreviewedLocation());
    }
}
