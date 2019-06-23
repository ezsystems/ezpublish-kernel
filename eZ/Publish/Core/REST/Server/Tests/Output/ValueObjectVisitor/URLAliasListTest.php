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
use eZ\Publish\Core\REST\Server\Values\URLAliasList;
use eZ\Publish\API\Repository\Values\Content;

class URLAliasListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLAliasList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList([], '/content/urlaliases');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UrlAliasList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
            ],
            $result,
            'Invalid <UrlAliasList> element.',
            false
        );
    }

    /**
     * Test if result contains UrlAliasList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UrlAliasList+xml',
                    'href' => '/content/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliasList> attributes.',
            false
        );
    }

    /**
     * Test if URLAliasList visitor visits the children.
     */
    public function testURLAliasListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList(
            [
                new Content\URLAlias(),
                new Content\URLAlias(),
            ],
            '/content/urlaliases'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Content\URLAlias::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );
    }

    /**
     * Get the URLAliasList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLAliasList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\URLAliasList();
    }
}
