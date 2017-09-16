<?php

/**
 * File containing the RootLocationIdCalculatorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\RootLocationIdCalculator;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

class RootLocationIdCalculatorTest extends TestCase
{
    private $configResolver;

    private $locationService;

    private $rootLocationIdCalculator;

    public function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock(ConfigResolverInterface::class);
        $this->locationService = $this->getMock(LocationService::class);

        $this->rootLocationIdCalculator = new RootLocationIdCalculator(
            $this->configResolver,
            $this->locationService
        );
    }

    /**
     * @test
     */
    public function testGetRootLocationIdWhenUsingLocationId()
    {
        $rootLocationId = 123;

        $this->configResolver
            ->expects($this->once())
            ->method('hasParameter')
            ->with('content.tree_root.location_id')
            ->will(
                $this->returnValue(true)
            );

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('content.tree_root.location_id')
            ->will(
                $this->returnValue($rootLocationId)
            );

        $this->locationService
            ->expects($this->never())
            ->method($this->anything());

        $this->assertSame($rootLocationId, $this->rootLocationIdCalculator->getRootLocationId());
    }

    /**
     * @test
     */
    public function testGetRootLocationIdWhenUsingLocationRemoteId()
    {
        $rootLocationId = 123;
        $rootLocationRemoteId = 'theRemoteLocationId';
        $rootLocation = new Location(['id' => $rootLocationId]);

        $this->configResolver
            ->expects($this->once())
            ->method('hasParameter')
            ->with('content.tree_root.location_id')
            ->will(
                $this->returnValue(false)
            );

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('content.tree_root.location_remote_id')
            ->will(
                $this->returnValue($rootLocationRemoteId)
            );

        $this->locationService
            ->expects($this->once())
            ->method('loadLocationByRemoteId')
            ->with($rootLocationRemoteId)
            ->will(
                $this->returnValue($rootLocation)
            );

        $this->assertSame($rootLocationId, $this->rootLocationIdCalculator->getRootLocationId());
    }

    /**
     * @test
     */
    public function testGetRootLocationIdBySiteaccessWhenUsingLocationId()
    {
        $rootLocationId = 123;
        $siteAccessName = 'foo';

        $this->configResolver
            ->expects($this->once())
            ->method('hasParameter')
            ->with('content.tree_root.location_id', null, 'foo')
            ->will(
                $this->returnValue(true)
            );

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('content.tree_root.location_id', null, 'foo')
            ->will(
                $this->returnValue($rootLocationId)
            );

        $this->locationService
            ->expects($this->never())
            ->method($this->anything());

        $this->assertSame($rootLocationId, $this->rootLocationIdCalculator->getRootLocationIdBySiteaccess($siteAccessName));
    }

    /**
     * @test
     */
    public function testGetRootLocationIdBySiteaccessWhenUsingLocationRemoteId()
    {
        $rootLocationId = 123;
        $siteAccessName = 'foo';
        $rootLocationRemoteId = 'theRemoteLocationId';
        $rootLocation = new Location(['id' => $rootLocationId]);

        $this->configResolver
            ->expects($this->once())
            ->method('hasParameter')
            ->with('content.tree_root.location_id', null, 'foo')
            ->will(
                $this->returnValue(false)
            );

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('content.tree_root.location_remote_id', null, 'foo')
            ->will(
                $this->returnValue($rootLocationRemoteId)
            );

        $this->locationService
            ->expects($this->once())
            ->method('loadLocationByRemoteId')
            ->with($rootLocationRemoteId)
            ->will(
                $this->returnValue($rootLocation)
            );

        $this->assertSame($rootLocationId, $this->rootLocationIdCalculator->getRootLocationIdBySiteaccess($siteAccessName));
    }
}
