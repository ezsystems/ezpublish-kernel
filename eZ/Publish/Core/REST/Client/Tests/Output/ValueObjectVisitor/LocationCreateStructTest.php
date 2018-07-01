<?php

/**
 * File containing a LocationCreateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\Location;

class LocationCreateStructTest extends ValueObjectVisitorBaseTest
{
    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit\Framework\MockObject\MockObject */
    private $locationServiceMock;

    /**
     * Tests the LocationCreateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $locationCreateStruct = new LocationCreateStruct();
        $locationCreateStruct->hidden = false;
        $locationCreateStruct->parentLocationId = 42;
        $locationCreateStruct->priority = 0;
        $locationCreateStruct->remoteId = 'remote-id';
        $locationCreateStruct->sortField = Location::SORT_FIELD_PATH;
        $locationCreateStruct->sortOrder = Location::SORT_ORDER_ASC;

        $this->locationServiceMock
            ->expects($this->once())
            ->method('loadLocation')
            ->with(42)
            ->will($this->returnValue(new Location(['pathString' => '/1/2/42'])));

        $this->getRouterMock()
            ->expects($this->once())
            ->method('generate')
            ->with('ezpublish_rest_loadLocation', ['locationPath' => '1/2/42'])
            ->will($this->returnValue('/content/locations/1/2/42'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $locationCreateStruct
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the result contains LocationCreate element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationCreateElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'LocationCreate',
                'children' => array(
                    'count' => 5,
                ),
            ),
            $result,
            'Invalid <LocationCreate> element.',
            false
        );
    }

    /**
     * Tests that the result contains LocationCreate attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationCreateAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'LocationCreate',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.LocationCreate+xml',
                ),
            ),
            $result,
            'Invalid <LocationCreate> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains ParentLocation element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'ParentLocation',
            ),
            $result,
            'Invalid <ParentLocation> element.',
            false
        );
    }

    /**
     * Tests that the result contains ParentLocation attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'ParentLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/2/42',
                ),
            ),
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains priority value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'priority',
                'content' => '0',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> priority value element.',
            false
        );
    }

    /**
     * Tests that the result contains hidden value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'hidden',
                'content' => 'false',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> hidden value element.',
            false
        );
    }

    /**
     * Tests that the result contains sortField value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'sortField',
                'content' => 'PATH',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> sortField value element.',
            false
        );
    }

    /**
     * Tests that the result contains sortOrder value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'sortOrder',
                'content' => 'ASC',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> sortOrder value element.',
            false
        );
    }

    /**
     * Gets the LocationCreateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\LocationCreateStruct
     */
    protected function internalGetVisitor()
    {
        $this->locationServiceMock = $this->createMock(LocationService::class);
        return new ValueObjectVisitor\LocationCreateStruct($this->locationServiceMock);
    }
}
