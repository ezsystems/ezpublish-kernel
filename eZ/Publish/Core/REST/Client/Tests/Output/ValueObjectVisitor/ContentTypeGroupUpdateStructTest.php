<?php
/**
 * File containing a ContentTypeGroupUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common;

class ContentTypeGroupUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the ContentTypeGroupUpdateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getContentTypeGroupUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeGroupUpdateStruct = new ContentType\ContentTypeGroupUpdateStruct();
        $contentTypeGroupUpdateStruct->identifier = 'some-group';
        $contentTypeGroupUpdateStruct->modificationDate = new \DateTime( '2013-02-22 14:14 Europe/Zagreb' );
        $contentTypeGroupUpdateStruct->modifierId = '/user/users/14';

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupUpdateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains identifier value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'identifier',
                'content' => 'some-group',
            ),
            $result,
            'Invalid or non-existing <ContentTypeGroupInput> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains modificationDate value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModificationDateDateValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'modificationDate',
                'content' => '2013-02-22T14:14:00+01:00',
            ),
            $result,
            'Invalid or non-existing <ContentTypeGroupInput> modificationDate value element.',
            false
        );
    }

    /**
     * Test if result contains User element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'User'
            ),
            $result,
            'Invalid <User> element.',
            false
        );
    }

    /**
     * Test if result contains User element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'User',
                'attributes' => array(
                    'href' => '/user/users/14',
                    'media-type' => 'application/vnd.ez.api.User+xml'
                )
            ),
            $result,
            'Invalid <User> element attributes.',
            false
        );
    }

    /**
     * Gets the ContentTypeGroupUpdateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\ContentTypeGroupUpdateStruct
     */
    protected function getContentTypeGroupUpdateStructVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroupUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
