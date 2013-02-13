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

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\ContentList;
use eZ\Publish\Core\REST\Server\Values\RestContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Common;

class ContentListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getContentListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentList = new ContentList( array() );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ContentList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentList',
            ),
            $result,
            'Invalid <ContentList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentList+xml',
                    'href'       => '/content/objects',
                )
            ),
            $result,
            'Invalid <ContentList> attributes.',
            false
        );
    }

    /**
     * Test if ContentList visitor visits the children
     */
    public function testContentListVisitsChildren()
    {
        $visitor   = $this->getContentListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentList = new ContentList(
            array(
                new RestContent( new ContentInfo() ),
                new RestContent( new ContentInfo() ),
            )
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\Rest\\Server\\Values\\RestContent' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );
    }

    /**
     * Get the ContentList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentList
     */
    protected function getContentListVisitor()
    {
        return new ValueObjectVisitor\ContentList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
