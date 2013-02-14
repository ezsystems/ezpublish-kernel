<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList;
use eZ\Publish\Core\REST\Common;

class ContentTypeGroupRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeGroupRefList visitor
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor   = $this->getContentTypeGroupRefListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeGroupRefList = new ContentTypeGroupRefList(
            new ContentType(
                array(
                    'id' => 42,
                    'fieldDefinitions' => array()
                )
            ),
            array(
                new ContentTypeGroup(
                    array(
                        'id' => 1
                    )
                ),
                new ContentTypeGroup(
                    array(
                        'id' => 2
                    )
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupRefList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeGroupRefListHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList[@href="/content/types/42/groups"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeGroupRefListMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList[@media-type="application/vnd.ez.api.ContentTypeGroupRefList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@href="/content/typegroups/1"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@media-type="application/vnd.ez.api.ContentTypeGroup+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@href="/content/types/42/groups/1"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkMethodCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@method="DELETE"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@href="/content/typegroups/2"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@media-type="application/vnd.ez.api.ContentTypeGroup+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@href="/content/types/42/groups/2"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkMethodCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@method="DELETE"]'  );
    }

    /**
     * Get the ContentTypeGroupRefList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeGroupRefList
     */
    protected function getContentTypeGroupRefListVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroupRefList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
