<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeUpdate;

class ContentTypeUpdateTest extends BaseTest
{
    /**
     * Tests the ContentTypeUpdate parser
     */
    public function testParse()
    {
        $inputArray = $this->getInputArray();

        $contentTypeUpdate = $this->getContentTypeUpdate();
        $result = $contentTypeUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeUpdateStruct',
            $result,
            'ContentTypeUpdateStruct not created correctly.'
        );

        $this->assertEquals(
            'updated_content_type',
            $result->identifier,
            'identifier not created correctly'
        );

        $this->assertEquals(
            'eng-US',
            $result->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        $this->assertEquals(
            'remote123456',
            $result->remoteId,
            'remoteId not created correctly'
        );

        $this->assertEquals(
            '<title>',
            $result->urlAliasSchema,
            'urlAliasSchema not created correctly'
        );

        $this->assertEquals(
            '<title>',
            $result->nameSchema,
            'nameSchema not created correctly'
        );

        $this->assertEquals(
            true,
            $result->isContainer,
            'isContainer not created correctly'
        );

        $this->assertEquals(
            Location::SORT_FIELD_PATH,
            $result->defaultSortField,
            'defaultSortField not created correctly'
        );

        $this->assertEquals(
            Location::SORT_ORDER_ASC,
            $result->defaultSortOrder,
            'defaultSortOrder not created correctly'
        );

        $this->assertEquals(
            true,
            $result->defaultAlwaysAvailable,
            'defaultAlwaysAvailable not created correctly'
        );

        $this->assertEquals(
            array( 'eng-US' => 'Updated content type' ),
            $result->names,
            'names not created correctly'
        );

        $this->assertEquals(
            array( 'eng-US' => 'Updated content type description' ),
            $result->descriptions,
            'descriptions not created correctly'
        );

        $this->assertEquals(
            new \DateTime( '2012-12-31T12:30:00' ),
            $result->modificationDate,
            'creationDate not created correctly'
        );

        $this->assertEquals(
            14,
            $result->modifierId,
            'creatorId not created correctly'
        );
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid names
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'names' element for ContentTypeUpdate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['names']['value'] );

        $contentTypeUpdate = $this->getContentTypeUpdate();
        $contentTypeUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid descriptions
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'descriptions' element for ContentTypeUpdate.
     */
    public function testParseExceptionOnInvalidDescriptions()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['descriptions']['value'] );

        $contentTypeUpdate = $this->getContentTypeUpdate();
        $contentTypeUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid User
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for User element in ContentTypeUpdate.
     */
    public function testParseExceptionOnInvalidUser()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['User']['_href'] );

        $contentTypeUpdate = $this->getContentTypeUpdate();
        $contentTypeUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ContentTypeUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeUpdate
     */
    protected function getContentTypeUpdate()
    {
        return new ContentTypeUpdate(
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
            ->method( 'newContentTypeUpdateStruct' )
            ->will( $this->returnValue( new ContentTypeUpdateStruct() ) );

        return $contentTypeServiceMock;
    }

    /**
     * Returns the array under test
     *
     * @return array
     */
    protected function getInputArray()
    {
        return array(
            'identifier' => 'updated_content_type',
            'mainLanguageCode' => 'eng-US',
            'remoteId' => 'remote123456',
            'urlAliasSchema' => '<title>',
            'nameSchema' => '<title>',
            'isContainer' => 'true',
            'defaultSortField' => 'PATH',
            'defaultSortOrder' => 'ASC',
            'defaultAlwaysAvailable' => 'true',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Updated content type'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Updated content type description'
                    )
                )
            ),
            'modificationDate' => '2012-12-31T12:30:00',
            'User' => array(
                '_href' => '/user/users/14'
            )
        );
    }
}
