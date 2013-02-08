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
use eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList;
use eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\Core\REST\Common;

class ContentTypeInfoListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeInfoList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getContentTypeInfoListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeInfoList = new ContentTypeInfoList( array(), '/content/typegroups/2/types' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeInfoList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains ContentTypeInfoList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeInfoList',
            ),
            $result,
            'Invalid <ContentTypeInfoList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeInfoList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentTypeInfoList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                    'href'       => '/content/typegroups/2/types',
                )
            ),
            $result,
            'Invalid <ContentTypeInfoList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeInfoList visitor visits the children
     */
    public function testContentTypeInfoListVisitsChildren()
    {
        $visitor   = $this->getContentTypeInfoListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeInfoList = new ContentTypeInfoList(
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
            $contentTypeInfoList
        );
    }

    /**
     * Get the ContentTypeInfoList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeInfoList
     */
    protected function getContentTypeInfoListVisitor()
    {
        return new ValueObjectVisitor\ContentTypeInfoList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
