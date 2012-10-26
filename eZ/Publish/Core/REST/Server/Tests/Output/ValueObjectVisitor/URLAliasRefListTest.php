<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\URLAliasRefList;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\REST\Common;

class URLAliasRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLAliasRefList visitor
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor   = $this->getURLAliasRefListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $urlAliasRefList = new URLAliasRefList(
            array(
                new URLAlias(
                    array(
                        'id' => 'some-id'
                    )
                )
            ),
            '/some/path'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasRefList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     * @depends testVisit
     */
    public function testUrlAliasRefListHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UrlAliasRefList[@href="/some/path"]'  );
    }

    /**
     * @param \DOMDocument $dom
     * @depends testVisit
     */
    public function testUrlAliasRefListMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UrlAliasRefList[@media-type="application/vnd.ez.api.UrlAliasRefList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     * @depends testVisit
     */
    public function testUrlAliasHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UrlAliasRefList/UrlAlias[@href="/content/urlaliases/some-id"]'  );
    }

    /**
     * @param \DOMDocument $dom
     * @depends testVisit
     */
    public function testUrlAliasMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UrlAliasRefList/UrlAlias[@media-type="application/vnd.ez.api.UrlAlias+xml"]'  );
    }

    /**
     * Get the URLAliasRefList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\URLAliasRefList
     */
    protected function getURLAliasRefListVisitor()
    {
        return new ValueObjectVisitor\URLAliasRefList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
