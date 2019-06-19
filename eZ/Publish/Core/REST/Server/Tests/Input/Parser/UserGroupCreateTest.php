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
use eZ\Publish\Core\REST\Server\Input\Parser\UserGroupCreate;
use eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;

class UserGroupCreateTest extends BaseTest
{
    /**
     * Tests the UserGroupCreate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $result = $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            UserGroupCreateStruct::class,
            $result,
            'UserGroupCreateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            \eZ\Publish\API\Repository\Values\ContentType\ContentType::class,
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

        foreach ($result->fields as $field) {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid ContentType.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ContentType element in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidContentType()
    {
        $inputArray = [
            'ContentType' => [],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing mainLanguageCode.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'mainLanguageCode' element for UserGroupCreate.
     */
    public function testParseExceptionOnMissingMainLanguageCode()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid Section.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidSection()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid fields data.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'fields' element for UserGroupCreate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserGroupCreate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on invalid field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage 'unknown' is invalid field definition identifier for 'some_class' content type in UserGroupCreate.
     */
    public function testParseExceptionOnInvalidFieldDefinitionIdentifier()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupCreate parser throwing exception on missing field value.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'name' identifier in UserGroupCreate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the UserGroupCreate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupCreate
     */
    protected function internalGetParser()
    {
        return new UserGroupCreate(
            $this->getUserServiceMock(),
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock()
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
            ->disableOriginalConstructor()
            ->setMethods([])
            ->setConstructorArgs(
                [
                    $this->createMock(ContentService::class),
                    $this->getContentTypeServiceMock(),
                    $this->createMock(FieldTypeService::class),
                ]
            )
            ->getMock();

        $fieldTypeParserMock->expects($this->any())
            ->method('parseValue')
            ->with('ezstring', [])
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
            ->method('newUserGroupCreateStruct')
            ->with(
                $this->equalTo('eng-US'),
                $this->equalTo($contentType)
            )
            ->will(
                $this->returnValue(
                    new UserGroupCreateStruct(
                        [
                            'contentType' => $contentType,
                            'mainLanguageCode' => 'eng-US',
                        ]
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
            ->with($this->equalTo(3))
            ->will($this->returnValue($this->getContentType()));

        return $contentTypeServiceMock;
    }

    /**
     * Get the content type used in UserGroupCreate parser.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function getContentType()
    {
        return new ContentType(
            [
                'id' => 3,
                'identifier' => 'some_class',
                'fieldDefinitions' => [
                    new FieldDefinition(
                        [
                            'id' => 42,
                            'identifier' => 'name',
                            'fieldTypeIdentifier' => 'ezstring',
                        ]
                    ),
                ],
            ]
        );
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/content/types/3', 'contentTypeId', 3],
            ['/content/sections/4', 'sectionId', 4],
        ];
    }
}
