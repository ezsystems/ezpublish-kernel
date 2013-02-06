<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeGroupInput;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

class ContentTypeGroupInputTest extends BaseTest
{
    /**
     * Tests the ContentTypeGroupInput parser
     */
    public function testParse()
    {
        $inputArray = array(
            'identifier' => 'Identifier Bar',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'modificationDate' => '2012-12-31T12:00:00'
        );

        $contentTypeGroupInput = $this->getContentTypeGroupInput();
        $result = $contentTypeGroupInput->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroupCreateStruct',
            $result,
            'ContentTypeGroupCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'Identifier Bar',
            $result->identifier,
            'ContentTypeGroupCreateStruct identifier property not created correctly.'
        );

        $this->assertEquals(
            14,
            $result->creatorId,
            'ContentTypeGroupCreateStruct creatorId property not created correctly.'
        );

        $this->assertEquals(
            new \DateTime( '2012-12-31T12:00:00' ),
            $result->creationDate,
            'ContentTypeGroupCreateStruct creationDate property not created correctly.'
        );
    }

    /**
     * Test ContentTypeGroupInput parser throwing exception on invalid User
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for User element in ContentTypeGroupInput.
     */
    public function testParseExceptionOnInvalidUser()
    {
        $inputArray = array(
            'identifier' => 'Identifier Bar',
            'User' => array(),
            'modificationDate' => '2012-12-31T12:00:00'
        );

        $contentTypeGroupInput = $this->getContentTypeGroupInput();
        $contentTypeGroupInput->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the content type group input parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeGroupInput
     */
    protected function getContentTypeGroupInput()
    {
        return new ContentTypeGroupInput(
            $this->getUrlHandler(),
            $this->getContentTypeServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the content type service mock object
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    protected function getContentTypeServiceMock()
    {
        $contentTypeServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\ContentTypeService',
            array(),
            array(),
            '',
            false
        );

        $contentTypeServiceMock->expects( $this->any() )
            ->method( 'newContentTypeGroupCreateStruct' )
            ->with( $this->equalTo( 'Identifier Bar' ) )
            ->will(
                $this->returnValue( new ContentTypeGroupCreateStruct( array( 'identifier' => 'Identifier Bar' ) ) )
            );

        return $contentTypeServiceMock;
    }
}
