<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RestContent;
use eZ\Publish\Core\REST\Server\Values\RestTrashItem;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestTrashItemTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the TrashItem visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $trashItem = new RestTrashItem(
            new TrashItem(
                [
                    'id' => 42,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 21,
                    'pathString' => '/1/2/21/42/',
                    'depth' => 3,
                    'contentInfo' => new ContentInfo(
                        [
                            'id' => 84,
                             'contentTypeId' => 4,
                             'name' => 'A Node, long lost in the trash',
                        ]
                    ),
                    'sortField' => TrashItem::SORT_FIELD_NAME,
                    'sortOrder' => TrashItem::SORT_ORDER_DESC,
                ]
            ),
            // Dummy value for ChildCount
            0
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadTrashItem',
            ['trashItemId' => $trashItem->trashItem->id],
            "/content/trash/{$trashItem->trashItem->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            ['locationPath' => '1/2/21'],
            '/content/locations/1/2/21'
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $trashItem->trashItem->contentInfo->id],
            "/content/objects/{$trashItem->trashItem->contentInfo->id}"
        );

        // Expected twice, second one here for ContentInfo
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $trashItem->trashItem->contentInfo->id],
            "/content/objects/{$trashItem->trashItem->contentInfo->id}"
        );

        $this->getVisitorMock()->expects($this->once())
            ->method('visitValueObject')
            ->with($this->isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trashItem
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains TrashItem element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashItemElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'TrashItem',
                'children' => [
                    'count' => 12,
                ],
            ],
            $result,
            'Invalid <TrashItem> element.',
            false
        );
    }

    /**
     * Test if result contains TrashItem element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashItemAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'TrashItem',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.TrashItem+xml',
                    'href' => '/content/trash/42',
                ],
            ],
            $result,
            'Invalid <TrashItem> attributes.',
            false
        );
    }

    /**
     * Test if result contains ContentInfo element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentInfoElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
            ],
            $result,
            'Invalid <ContentInfo> element.',
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
    public function testResultContainsContentInfoAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/84',
                ],
            ],
            $result,
            'Invalid <ContentInfo> attributes.',
            false
        );
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
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <TrashItem> id value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> priority value element.',
            false
        );
    }

    /**
     * Test if result contains hidden value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'hidden',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <TrashItem> hidden value element.',
            false
        );
    }

    /**
     * Test if result contains invisible value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsInvisibleValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'invisible',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <TrashItem> invisible value element.',
            false
        );
    }

    /**
     * Test if result contains remoteId value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRemoteIdValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'remoteId',
                'content' => 'remote-id',
            ],
            $result,
            'Invalid or non-existing <TrashItem> remoteId value element.',
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
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/2/21',
                ],
            ],
            $result,
            'Invalid <ParentLocation> attributes.',
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
                'content' => '/1/2/21/42/',
            ],
            $result,
            'Invalid or non-existing <TrashItem> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains depth value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDepthValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'depth',
                'content' => '3',
            ],
            $result,
            'Invalid or non-existing <TrashItem> depth value element.',
            false
        );
    }

    /**
     * Test if result contains childCount value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildCountValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'childCount',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> childCount value element.',
            false
        );
    }

    /**
     * Test if result contains Content element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
            ],
            $result,
            'Invalid <Content> element.',
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
    public function testResultContainsContentAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Content+xml',
                    'href' => '/content/objects/84',
                ],
            ],
            $result,
            'Invalid <Content> attributes.',
            false
        );
    }

    /**
     * Test if result contains sortField value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortField',
                'content' => 'NAME',
            ],
            $result,
            'Invalid or non-existing <TrashItem> sortField value element.',
            false
        );
    }

    /**
     * Test if result contains sortOrder value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortOrder',
                'content' => 'DESC',
            ],
            $result,
            'Invalid or non-existing <TrashItem> sortOrder value element.',
            false
        );
    }

    /**
     * Get the TrashItem visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestTrashItem
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestTrashItem();
    }
}
