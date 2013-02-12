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
use eZ\Publish\Core\REST\Server\Values\ContentTypeList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\Core\REST\Common;

class ContentTypeListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getContentTypeListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeList = new ContentTypeList( array(), '/content/typegroups/2/types' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ContentTypeList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeList',
            ),
            $result,
            'Invalid <ContentTypeList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeList+xml',
                    'href'       => '/content/typegroups/2/types',
                )
            ),
            $result,
            'Invalid <ContentTypeList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeList visitor visits the children
     */
    public function testContentTypeListVisitsChildren()
    {
        $visitor   = $this->getContentTypeListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeList = new ContentTypeList(
            array(
                new ContentType\ContentType(
                    array(
                        'fieldDefinitions' => array()
                    )
                ),
                new ContentType\ContentType(
                    array(
                        'fieldDefinitions' => array()
                    )
                ),
            ),
            '/content/typegroups/2/types'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\RestContentType' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeList
        );
    }

    /**
     * Get the ContentTypeList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeList
     */
    protected function getContentTypeListVisitor()
    {
        return new ValueObjectVisitor\ContentTypeList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
