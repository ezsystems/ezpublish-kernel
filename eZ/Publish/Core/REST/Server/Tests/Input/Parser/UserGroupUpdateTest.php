<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\FieldTypeService;
use eZ\Publish\Core\Repository\LocationService;
use eZ\Publish\Core\Repository\UserService;
use eZ\Publish\Core\REST\Server\Input\Parser\UserGroupUpdate;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Server\Values\RestUserGroupUpdateStruct;

class UserGroupUpdateTest extends BaseTest
{
    /**
     * Tests the UserGroupUpdate parser.
     */
    public function testParse()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1',
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
            '__url' => '/user/groups/1/5',
        );

        $userGroupUpdate = $this->getParser();
        $result = $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            RestUserGroupUpdateStruct::class,
            $result,
            'UserGroupUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            ContentUpdateStruct::class,
            $result->userGroupUpdateStruct->contentUpdateStruct,
            'UserGroupUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            ContentMetadataUpdateStruct::class,
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct,
            'UserGroupUpdate not created correctly.'
        );

        $this->assertEquals(
            1,
            $result->sectionId,
            'sectionId not created correctly'
        );

        $this->assertEquals(
            'eng-US',
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        $this->assertEquals(
            'remoteId123456',
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct->remoteId,
            'remoteId not created correctly'
        );

        foreach ($result->userGroupUpdateStruct->contentUpdateStruct->fields as $field) {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing Section href.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in UserGroupUpdate.
     */
    public function testParseExceptionOnMissingSectionHref()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array(),
                    ),
                ),
            ),
            '__url' => '/user/groups/1/5',
        );

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on invalid fields data.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'fields' element for UserGroupUpdate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1',
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(),
            '__url' => '/user/groups/1/5',
        );

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field definition identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserGroupUpdate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1',
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array(),
                    ),
                ),
            ),
            '__url' => '/user/groups/1/5',
        );

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field value.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'name' identifier in UserGroupUpdate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1',
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                    ),
                ),
            ),
            '__url' => '/user/groups/1/5',
        );

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the UserGroupUpdate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupUpdate
     */
    protected function internalGetParser()
    {
        return new UserGroupUpdate(
            $this->getUserServiceMock(),
            $this->getContentServiceMock(),
            $this->getLocationServiceMock(),
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
            ->setMethods(array())
            ->setConstructorArgs(
                array(
                    $this->getContentServiceMock(),
                    $this->createMock(ContentTypeService::class),
                    $this->createMock(FieldTypeService::class),
                )
            )
            ->getMock();

        $fieldTypeParserMock->expects($this->any())
            ->method('parseFieldValue')
            ->with(4, 'name', array())
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

        $userServiceMock->expects($this->any())
            ->method('newUserGroupUpdateStruct')
            ->will(
                $this->returnValue(new UserGroupUpdateStruct())
            );

        return $userServiceMock;
    }

    /**
     * Get the location service mock object.
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    protected function getLocationServiceMock()
    {
        $userServiceMock = $this->createMock(LocationService::class);

        $userServiceMock->expects($this->any())
            ->method('loadLocation')
            ->with($this->equalTo(5))
            ->will(
                $this->returnValue(
                    new Location(
                        array(
                            'contentInfo' => new ContentInfo(
                                array(
                                    'id' => 4,
                                )
                            ),
                        )
                    )
                )
            );

        return $userServiceMock;
    }

    /**
     * Get the content service mock object.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    protected function getContentServiceMock()
    {
        $contentServiceMock = $this->createMock(ContentService::class);

        $contentServiceMock->expects($this->any())
            ->method('newContentUpdateStruct')
            ->will(
                $this->returnValue(new ContentUpdateStruct())
            );

        $contentServiceMock->expects($this->any())
            ->method('newContentMetadataUpdateStruct')
            ->will(
                $this->returnValue(new ContentMetadataUpdateStruct())
            );

        return $contentServiceMock;
    }

    public function getParseHrefExpectationsMap()
    {
        return array(
            array('/content/sections/1', 'sectionId', 1),
            array('/user/groups/1/5', 'groupPath', '1/5'),
        );
    }
}
