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
use eZ\Publish\Core\REST\Server\Values\ContentTypeList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\Core\REST\Server\Values\RestContentType;

class ContentTypeListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeList = new ContentTypeList([], '/content/typegroups/2/types');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeList',
            ],
            $result,
            'Invalid <ContentTypeList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentTypeList+xml',
                    'href' => '/content/typegroups/2/types',
                ],
            ],
            $result,
            'Invalid <ContentTypeList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeList visitor visits the children.
     */
    public function testContentTypeListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeList = new ContentTypeList(
            [
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => [],
                    ]
                ),
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => [],
                    ]
                ),
            ],
            '/content/typegroups/2/types'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(RestContentType::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeList
        );
    }

    /**
     * Get the ContentTypeList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentTypeList();
    }
}
