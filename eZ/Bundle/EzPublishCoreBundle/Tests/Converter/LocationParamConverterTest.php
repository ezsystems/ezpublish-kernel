<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Converter;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\LocationService;
use eZ\Bundle\EzPublishCoreBundle\Converter\LocationParamConverter;
use Symfony\Component\HttpFoundation\Request;

class LocationParamConverterTest extends AbstractParamConverterTest
{
    const PROPERTY_NAME = 'locationId';

    const LOCATION_CLASS = Location::class;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Converter\LocationParamConverter */
    private $converter;

    private $locationServiceMock;

    public function setUp()
    {
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $this->converter = new LocationParamConverter($this->locationServiceMock);
    }

    public function testSupports()
    {
        $config = $this->createConfiguration(self::LOCATION_CLASS);
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApplyLocation()
    {
        $id = 42;
        $valueObject = $this->createMock(Location::class);

        $this->locationServiceMock
            ->expects($this->once())
            ->method('loadLocation')
            ->with($id)
            ->will($this->returnValue($valueObject));

        $request = new Request([], [], [self::PROPERTY_NAME => $id]);
        $config = $this->createConfiguration(self::LOCATION_CLASS, 'location');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf(self::LOCATION_CLASS, $request->attributes->get('location'));
    }

    public function testApplyLocationOptionalWithEmptyAttribute()
    {
        $request = new Request([], [], [self::PROPERTY_NAME => null]);
        $config = $this->createConfiguration(self::LOCATION_CLASS, 'location');

        $config->expects($this->once())
            ->method('isOptional')
            ->will($this->returnValue(true));

        $this->assertFalse($this->converter->apply($request, $config));
        $this->assertNull($request->attributes->get('location'));
    }
}
