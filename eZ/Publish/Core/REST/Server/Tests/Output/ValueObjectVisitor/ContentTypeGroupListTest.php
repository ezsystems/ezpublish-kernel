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
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\Core\REST\Common;

class ContentTypeGroupListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeGroupList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getContentTypeGroupListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeGroupList = new ContentTypeGroupList( array() );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ContentTypeGroupList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeGroupList',
            ),
            $result,
            'Invalid <ContentTypeGroupList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeGroupList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeGroupList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeGroupList+xml',
                    'href'       => '/content/typegroups',
                )
            ),
            $result,
            'Invalid <ContentTypeGroupList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeGroupList visitor visits the children
     */
    public function testContentTypeGroupListVisitsChildren()
    {
        $visitor   = $this->getContentTypeGroupListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeGroupList = new ContentTypeGroupList(
            array(
                new ContentType\ContentTypeGroup(),
                new ContentType\ContentTypeGroup(),
            )
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );
    }

    /**
     * Get the ContentTypeGroupList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeGroupList
     */
    protected function getContentTypeGroupListVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroupList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
