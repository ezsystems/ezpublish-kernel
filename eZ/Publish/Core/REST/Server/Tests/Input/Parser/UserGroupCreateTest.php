<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\UserGroupCreate;

class UserGroupCreateTest extends BaseTest
{
    /**
     * Tests the UserGroupCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreate();
        $result = $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupCreateStruct',
            $result,
            'UserGroupCreateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $result->contentType,
            'contentType not created correctly.'
        );

        $this->assertEquals(
            3,
            $result->contentType->id,
            'contentType not created correctly'
        );

        $this->assertEquals(
            'eng-US',
            $result->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        $this->assertEquals(
            4,
            $result->sectionId,
            'sectionId not created correctly'
        );

        $this->assertEquals(
            'remoteId12345678',
            $result->remoteId,
            'remoteId not created correctly'
        );

        foreach ( $result->fields as $field )
        {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid ContentType
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ContentType element in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidContentType()
    {
        $inputArray = array(
            'ContentType' => array(),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing mainLanguageCode
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'mainLanguageCode' element for UserGroupCreate.
     */
    public function testParseExceptionOnMissingMainLanguageCode()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid Section
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidSection()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid fields data
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'fields' element for UserGroupCreate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserGroupCreate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    )
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage 'unknown' is invalid field definition identifier for 'comment' content type in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidFieldDefinitionIdentifier()
    {
        $this->markTestSkipped( '@todo Disabled due to invalid implementation of ContentType::getFieldDefinition in InMemory stub' );
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/3'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing field value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'subject' identifier in UserGroupCreate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $this->markTestSkipped( '@todo Disabled due to invalid implementation of ContentType::getFieldDefinition in InMemory stub' );
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/13'
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4'
            ),
            'remoteId' => 'remoteId12345678',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name'
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    ),
                )
            )
        );

        $userGroupCreate = $this->getUserGroupCreateWithSimpleFieldTypeMock();
        $userGroupCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the UserGroupCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupCreate
     */
    protected function getUserGroupCreate()
    {
        return new UserGroupCreate(
            $this->getUrlHandler(),
            $this->getRepository()->getUserService(),
            $this->getRepository()->getContentTypeService(),
            $this->getFieldTypeParserMock()
        );
    }

    /**
     * Returns the UserGroupCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupCreate
     */
    protected function getUserGroupCreateWithSimpleFieldTypeMock()
    {
        return new UserGroupCreate(
            $this->getUrlHandler(),
            $this->getRepository()->getUserService(),
            $this->getRepository()->getContentTypeService(),
            $this->getSimpleFieldTypeParserMock()
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
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Client\\ContentService',
                    array(),
                    array(),
                    '',
                    false
                ),
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
                    array(),
                    array(),
                    '',
                    false
                ),
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

        $fieldTypeParserMock->expects( $this->at( 0 ) )
            ->method( 'parseValue' )
            ->with( 'ezstring', array() )
            ->will( $this->returnValue( 'foo' ) );

        $fieldTypeParserMock->expects( $this->at( 1 ) )
            ->method( 'parseValue' )
            ->with( 'ezstring', array() )
            ->will( $this->returnValue( 'foo' ) );

        return $fieldTypeParserMock;
    }

    /**
     * Get the field type parser mock object
     *
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
     */
    private function getSimpleFieldTypeParserMock()
    {
        $fieldTypeParserMock = $this->getMock(
            '\\eZ\\Publish\\Core\\REST\\Common\\Input\\FieldTypeParser',
            array(),
            array(
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Client\\ContentService',
                    array(),
                    array(),
                    '',
                    false
                ),
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Client\\ContentTypeService',
                    array(),
                    array(),
                    '',
                    false
                ),
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

        return $fieldTypeParserMock;
    }
}
