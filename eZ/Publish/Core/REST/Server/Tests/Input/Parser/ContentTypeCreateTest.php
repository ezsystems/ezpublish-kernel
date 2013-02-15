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
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeCreate;
use eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate;

class ContentTypeCreateTest extends BaseTest
{
    /**
     * Tests the ContentTypeCreate parser
     */
    public function testParse()
    {
        $inputArray = $this->getInputArray();

        $contentTypeCreate = $this->getContentTypeCreate();
        $result = $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeCreateStruct',
            $result,
            'ContentTypeCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'new_content_type',
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
            array( 'eng-US' => 'New content type' ),
            $result->names,
            'names not created correctly'
        );

        $this->assertEquals(
            array( 'eng-US' => 'New content type description' ),
            $result->descriptions,
            'descriptions not created correctly'
        );

        $this->assertEquals(
            new \DateTime( '2012-12-31T12:30:00' ),
            $result->creationDate,
            'creationDate not created correctly'
        );

        $this->assertEquals(
            14,
            $result->creatorId,
            'creatorId not created correctly'
        );

        foreach ( $result->fieldDefinitions as $fieldDefinition )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinitionCreateStruct',
                $fieldDefinition,
                'ContentTypeCreateStruct field definition not created correctly.'
            );
        }
    }

    /**
     * Test ContentTypeCreate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' element for ContentTypeCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['identifier'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeCreate parser throwing exception on missing mainLanguageCode
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'mainLanguageCode' element for ContentTypeCreate.
     */
    public function testParseExceptionOnMissingMainLanguageCode()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['mainLanguageCode'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeCreate parser throwing exception on invalid names
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'names' element for ContentTypeCreate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['names']['value'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeCreate parser throwing exception on invalid descriptions
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'descriptions' element for ContentTypeCreate.
     */
    public function testParseExceptionOnInvalidDescriptions()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['descriptions']['value'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeCreate parser throwing exception on invalid User
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for User element in ContentTypeCreate.
     */
    public function testParseExceptionOnInvalidUser()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['User']['_href'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentTypeCreate parser throwing exception on invalid FieldDefinitions
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'FieldDefinitions' element for ContentTypeCreate.
     */
    public function testParseExceptionOnInvalidFieldDefinitions()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['FieldDefinitions']['FieldDefinition'] );

        $contentTypeCreate = $this->getContentTypeCreate();
        $contentTypeCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ContentTypeCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentTypeCreate
     */
    protected function getContentTypeCreate()
    {
        return new ContentTypeCreate(
            $this->getUrlHandler(),
            $this->getContentTypeServiceMock(),
            $this->getFieldDefinitionCreateParserMock(),
            $this->getParserTools()
        );
    }

    /**
     * Returns the FieldDefinitionCreate parser mock object
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate
     */
    private function getFieldDefinitionCreateParserMock()
    {
        $fieldDefinitionCreateParserMock = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Server\\Input\\Parser\\FieldDefinitionCreate',
            array(),
            array(),
            '',
            false
        );

        $fieldDefinitionCreateParserMock->expects( $this->any() )
            ->method( 'parse' )
            ->with( array(), $this->getParsingDispatcherMock() )
            ->will( $this->returnValue( new FieldDefinitionCreateStruct() ) );

        return $fieldDefinitionCreateParserMock;
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
            ->method( 'newContentTypeCreateStruct' )
            ->with( $this->equalTo( 'new_content_type' ) )
            ->will(
                $this->returnValue(
                    new ContentTypeCreateStruct(
                        array(
                            'identifier' => 'new_content_type'
                        )
                    )
                )
            );

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
            'identifier' => 'new_content_type',
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
                        '#text' => 'New content type'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'New content type description'
                    )
                )
            ),
            'modificationDate' => '2012-12-31T12:30:00',
            'User' => array(
                '_href' => '/user/users/14'
            ),

            // mocked
            'FieldDefinitions' => array(
                'FieldDefinition' => array(
                    array(),
                    array()
                )
            )
        );
    }
}
