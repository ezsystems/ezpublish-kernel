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
use eZ\Publish\Core\REST\Server\Values\URLWildcardList;
use eZ\Publish\API\Repository\Values\Content;

class URLWildcardListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLWildcardList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlWildcardList = new URLWildcardList([]);

        $this->addRouteExpectation(
            'ezpublish_rest_listURLWildcards',
            [],
            '/content/urlwildcards'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcardList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UrlWildcardList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcardList',
            ],
            $result,
            'Invalid <UrlWildcardList> element.',
            false
        );
    }

    /**
     * Test if result contains UrlWildcardList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcardList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UrlWildcardList+xml',
                    'href' => '/content/urlwildcards',
                ],
            ],
            $result,
            'Invalid <UrlWildcardList> attributes.',
            false
        );
    }

    /**
     * Test if URLWildcardList visitor visits the children.
     */
    public function testURLWildcardListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlWildcardList = new URLWildcardList(
            [
                new Content\URLWildcard(),
                new Content\URLWildcard(),
            ]
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Content\URLWildcard::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcardList
        );
    }

    /**
     * Get the URLWildcardList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLWildcardList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\URLWildcardList();
    }
}
