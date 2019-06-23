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
use eZ\Publish\Core\REST\Server\Values\ContentList;
use eZ\Publish\Core\REST\Server\Values\RestContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ContentListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentList = new ContentList([]);

        $this->addRouteExpectation(
            'ezpublish_rest_redirectContent',
            [],
            '/content/objects'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentList',
            ],
            $result,
            'Invalid <ContentList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentList+xml',
                    'href' => '/content/objects',
                ],
            ],
            $result,
            'Invalid <ContentList> attributes.',
            false
        );
    }

    /**
     * Test if ContentList visitor visits the children.
     */
    public function testContentListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentList = new ContentList(
            [
                new RestContent(new ContentInfo()),
                new RestContent(new ContentInfo()),
            ]
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );
    }

    /**
     * Get the ContentList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentList();
    }
}
