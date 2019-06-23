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
use eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\Core\REST\Server\Values\RestContentType;

class ContentTypeInfoListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeInfoList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeInfoList = new ContentTypeInfoList([], '/content/typegroups/2/types');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeInfoList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeInfoList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeInfoList',
            ],
            $result,
            'Invalid <ContentTypeInfoList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeInfoList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeInfoList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                    'href' => '/content/typegroups/2/types',
                ],
            ],
            $result,
            'Invalid <ContentTypeInfoList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeInfoList visitor visits the children.
     */
    public function testContentTypeInfoListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeInfoList = new ContentTypeInfoList(
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
            $contentTypeInfoList
        );
    }

    /**
     * Get the ContentTypeInfoList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeInfoList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentTypeInfoList();
    }
}
