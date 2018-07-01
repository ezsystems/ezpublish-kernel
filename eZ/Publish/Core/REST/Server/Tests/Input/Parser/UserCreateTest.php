<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\UserService;
use eZ\Publish\Core\REST\Client\ContentService;
use eZ\Publish\Core\REST\Client\FieldTypeService;
use eZ\Publish\Core\REST\Server\Input\Parser\UserCreate;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;

class UserCreateTest extends BaseTest
{
    /**
     * Tests the UserCreate parser.
     */
    public function testParse()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $result = $userCreate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            UserCreateStruct::class,
            $result,
            'UserCreateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            ContentType::class,
            $result->contentType,
            'contentType not created correctly.'
        );

        $this->assertEquals(
            4,
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

        foreach ($result->fields as $field) {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserCreate parser throwing exception on invalid ContentType.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ContentType element in UserCreate.
     */
    public function testParseExceptionOnInvalidContentType()
    {
        $inputArray = array(
            'ContentType' => array(),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing mainLanguageCode.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'mainLanguageCode' element for UserCreate.
     */
    public function testParseExceptionOnMissingMainLanguageCode()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing login.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'login' element for UserCreate.
     */
    public function testParseExceptionOnMissingLogin()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing email.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'email' element for UserCreate.
     */
    public function testParseExceptionOnMissingEmail()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing password.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'password' element for UserCreate.
     */
    public function testParseExceptionOnMissingPassword()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on invalid Section.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in UserCreate.
     */
    public function testParseExceptionOnInvalidSection()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on invalid fields data.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'fields' element for UserCreate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserCreate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array(),
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on invalid field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage 'unknown' is invalid field definition identifier for 'some_class' content type in UserCreate.
     */
    public function testParseExceptionOnInvalidFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => array(),
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserCreate parser throwing exception on missing field value.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'name' identifier in UserCreate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = array(
            'ContentType' => array(
                '_href' => '/content/types/4',
            ),
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/4',
            ),
            'remoteId' => 'remoteId12345678',
            'login' => 'login',
            'email' => 'nospam@ez.no',
            'password' => 'password',
            'enabled' => 'true',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                    ),
                ),
            ),
        );

        $userCreate = $this->getParser();
        $userCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the UserCreate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserCreate
     */
    protected function internalGetParser()
    {
        return new UserCreate(
            $this->getUserServiceMock(),
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the field type parser mock object.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
     */
    private function getFieldTypeParserMock()
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->setConstructorArgs(
                array(
                    $this->createMock(ContentService::class),
                    $this->getContentTypeServiceMock(),
                    $this->createMock(FieldTypeService::class),
                )
            )
            ->getMock();

        $fieldTypeParserMock->expects($this->any())
            ->method('parseValue')
            ->with('ezstring', array())
            ->will($this->returnValue('foo'));

        return $fieldTypeParserMock;
    }

    /**
     * Get the user service mock object.
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    protected function getUserServiceMock()
    {
        $userServiceMock = $this->createMock(UserService::class);

        $contentType = $this->getContentType();
        $userServiceMock->expects($this->any())
            ->method('newUserCreateStruct')
            ->with(
                $this->equalTo('login'),
                $this->equalTo('nospam@ez.no'),
                $this->equalTo('password'),
                $this->equalTo('eng-US'),
                $this->equalTo($contentType)
            )
            ->will(
                $this->returnValue(
                    new UserCreateStruct(
                        array(
                            'contentType' => $contentType,
                            'mainLanguageCode' => 'eng-US',
                        )
                    )
                )
            );

        return $userServiceMock;
    }

    /**
     * Get the content type service mock object.
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    protected function getContentTypeServiceMock()
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects($this->any())
            ->method('loadContentType')
            ->with($this->equalTo(4))
            ->will($this->returnValue($this->getContentType()));

        return $contentTypeServiceMock;
    }

    /**
     * Get the content type used in UserCreate parser.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function getContentType()
    {
        return new ContentType(
            array(
                'id' => 4,
                'identifier' => 'some_class',
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'id' => 42,
                            'identifier' => 'name',
                            'fieldTypeIdentifier' => 'ezstring',
                        )
                    ),
                ),
            )
        );
    }

    public function getParseHrefExpectationsMap()
    {
        return array(
            array('/content/types/4', 'contentTypeId', 4),
            array('/content/sections/4', 'sectionId', 4),
        );
    }
}
