<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RestContent;
use eZ\Publish\Core\REST\Server\Values\RestLocation;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestLocationRootNodeTest extends RestLocationTest
{
    /**
     * Test the Location visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $location = new RestLocation(
            new Location(
                [
                    'id' => 1,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => null,
                    'pathString' => '/1',
                    'depth' => 3,
                    'sortField' => Location::SORT_FIELD_PATH,
                    'sortOrder' => Location::SORT_ORDER_ASC,
                    'contentInfo' => new ContentInfo(
                        [
                            'id' => 42,
                            'contentTypeId' => 4,
                            'name' => 'A Node, long lost',
                        ]
                    ),
                ]
            ),
            // Dummy value for ChildCount
            0
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            ['locationPath' => '1'],
            '/content/locations/1'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocationChildren',
            ['locationPath' => '1'],
            '/content/locations/1/children'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $location->location->contentId],
            "/content/objects/{$location->location->contentId}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_listLocationURLAliases',
            ['locationPath' => '1'],
            '/content/objects/1/urlaliases'
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $location->location->contentId],
            "/content/objects/{$location->location->contentId}"
        );

        $this->getVisitorMock()->expects($this->once())
            ->method('visitValueObject')
            ->with($this->isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $location
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains id value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '1',
            ],
            $result,
            'Invalid or non-existing <Location> id value element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
            ],
            $result,
            'Invalid <ParentLocation> element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
                'attributes' => [],
            ],
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Test if result contains Location element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Location',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1',
                ],
            ],
            $result,
            'Invalid <Location> attributes.',
            false
        );
    }

    /**
     * Test if result contains Children element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Children',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.LocationList+xml',
                    'href' => '/content/locations/1/children',
                ],
            ],
            $result,
            'Invalid <Children> attributes.',
            false
        );
    }

    /**
     * Test if result contains pathString value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'pathString',
                'content' => '/1',
            ],
            $result,
            'Invalid or non-existing <Location> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasesTagAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliases',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UrlAliasRefList+xml',
                    'href' => '/content/objects/1/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliases> attributes.',
            false
        );
    }

    /**
     * Get the Location visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestLocation
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestLocation();
    }
}
