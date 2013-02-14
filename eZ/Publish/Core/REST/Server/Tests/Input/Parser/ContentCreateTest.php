<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentCreate;
use eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate;

class ContentCreateTest extends BaseTest
{
    /**
     * Tests the ContentCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $result = $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestContentCreateStruct',
            $result,
            'ContentCreate not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct',
            $result->contentCreateStruct,
            'contentCreateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $result->contentCreateStruct->contentType,
            'contentType not created correctly.'
        );

        $this->assertEquals(
            13,
            $result->contentCreateStruct->contentType->id,
            'contentType not created correctly'
        );

        $this->assertEquals(
            'eng-US',
            $result->contentCreateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct',
            $result->locationCreateStruct,
            'locationCreateStruct not created correctly.'
        );

        $this->assertEquals(
            4,
            $result->contentCreateStruct->sectionId,
            'sectionId not created correctly'
        );

        $this->assertEquals(
            true,
            $result->contentCreateStruct->alwaysAvailable,
            'alwaysAvailable not created correctly'
        );

        $this->assertEquals(
            'remoteId12345678',
            $result->contentCreateStruct->remoteId,
            'remoteId not created correctly'
        );

        $this->assertEquals(
            14,
            $result->contentCreateStruct->ownerId,
            'ownerId not created correctly'
        );

        foreach ( $result->contentCreateStruct->fields as $field )
        {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test ContentCreate parser throwing exception on missing LocationCreate
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'LocationCreate' element for ContentCreate.
     */
    public function testParseExceptionOnMissingLocationCreate()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on missing ContentType
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'ContentType' element for ContentCreate.
     */
    public function testParseExceptionOnMissingContentType()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on invalid ContentType
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ContentType element in ContentCreate.
     */
    public function testParseExceptionOnInvalidContentType()
    {
        $inputArray = array(
            'ContentType' => array(),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on missing mainLanguageCode
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'mainLanguageCode' element for ContentCreate.
     */
    public function testParseExceptionOnMissingMainLanguageCode()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on invalid Section
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in ContentCreate.
     */
    public function testParseExceptionOnInvalidSection()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on invalid User
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for User element in ContentCreate.
     */
    public function testParseExceptionOnInvalidUser()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on invalid fields data
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'fields' element for ContentCreate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on missing field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for ContentCreate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on invalid field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage 'unknown' is invalid field definition identifier for 'some_class' content type in ContentCreate.
     */
    public function testParseExceptionOnInvalidFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test ContentCreate parser throwing exception on missing field value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'subject' identifier in ContentCreate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => array(),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => array(
                '_href' => '/user/users/14'
            ),
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject'
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $contentCreate = $this->getContentCreate();
        $contentCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the ContentCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentCreate
     */
    protected function getContentCreate()
    {
        return new ContentCreate(
            $this->getUrlHandler(),
            $this->getContentServiceMock(),
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getLocationCreateParserMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the field type parser mock object
     *
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
     */
    private function getFieldTypeParserMock()
    {
        $fieldTypeParserMock = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Input\\FieldTypeParser',
            array(),
            array(
                $this->getContentServiceMock(),
                $this->getContentTypeServiceMock(),
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Client\\FieldTypeService',
                    array(),
                    array(),
                    '',
                    false
                )
            ),
            '',
            false
        );

        $fieldTypeParserMock->expects( $this->any() )
            ->method( 'parseValue' )
            ->with( 'ezstring', array() )
            ->will( $this->returnValue( 'foo' ) );

        return $fieldTypeParserMock;
    }

    /**
     * Returns the LocationCreate parser mock object
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate
     */
    private function getLocationCreateParserMock()
    {
        $locationCreateParserMock = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Server\\Input\\Parser\\LocationCreate',
            array(),
            array(),
            '',
            false
        );

        $locationCreateParserMock->expects( $this->any() )
            ->method( 'parse' )
            ->with( array(), $this->getParsingDispatcherMock() )
            ->will( $this->returnValue( new LocationCreateStruct() ) );

        return $locationCreateParserMock;
    }

    /**
     * Get the content service mock object
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    protected function getContentServiceMock()
    {
        $contentServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\ContentService',
            array(),
            array(),
            '',
            false
        );

        $contentType = $this->getContentType();
        $contentServiceMock->expects( $this->any() )
            ->method( 'newContentCreateStruct' )
            ->with(
                $this->equalTo( $contentType ),
                $this->equalTo( 'eng-US' )
            )
            ->will(
                $this->returnValue(
                    new ContentCreateStruct(
                        array(
                            'contentType' => $contentType,
                            'mainLanguageCode' => 'eng-US'
                        )
                    )
                )
            );

        return $contentServiceMock;
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
            ->method( 'loadContentType' )
            ->with( $this->equalTo( 13 ) )
            ->will( $this->returnValue( $this->getContentType() ) );

        return $contentTypeServiceMock;
    }

    /**
     * Get the content type used in ContentCreate parser
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function getContentType()
    {
        return new ContentType(
            array(
                'id' => 13,
                'identifier' => 'some_class',
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'id' => 42,
                            'identifier' => 'subject',
                            'fieldTypeIdentifier' => 'ezstring'
                        )
                    ),
                    new FieldDefinition(
                        array(
                            'id' => 43,
                            'identifier' => 'author',
                            'fieldTypeIdentifier' => 'ezstring'
                        )
                    )
                )
            )
        );
    }
}
