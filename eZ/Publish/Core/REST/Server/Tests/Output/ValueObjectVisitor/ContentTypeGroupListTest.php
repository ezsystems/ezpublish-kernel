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
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeGroupListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeGroupList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupList = new ContentTypeGroupList([]);

        $this->addRouteExpectation('ezpublish_rest_loadContentTypeGroupList', [], '/content/typegroups');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeGroupList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroupList',
            ],
            $result,
            'Invalid <ContentTypeGroupList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeGroupList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroupList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentTypeGroupList+xml',
                    'href' => '/content/typegroups',
                ],
            ],
            $result,
            'Invalid <ContentTypeGroupList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeGroupList visitor visits the children.
     */
    public function testContentTypeGroupListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupList = new ContentTypeGroupList(
            [
                new ContentType\ContentTypeGroup(),
                new ContentType\ContentTypeGroup(),
            ]
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(ContentTypeGroup::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );
    }

    /**
     * Get the ContentTypeGroupList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeGroupList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroupList();
    }
}
