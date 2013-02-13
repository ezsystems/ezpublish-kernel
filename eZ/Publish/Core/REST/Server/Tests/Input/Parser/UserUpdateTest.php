<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\UserUpdate;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;

class UserUpdateTest extends BaseTest
{
    /**
     * Tests the UserUpdate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1'
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'first_name',
                        'fieldValue' => array()
                    )
                )
            ),
            'email' => 'nospam@ez.no',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14'
        );

        $userUpdate = $this->getUserUpdate();
        $result = $userUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserUpdateStruct',
            $result,
            'UserUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct',
            $result->userUpdateStruct->contentUpdateStruct,
            'UserUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentMetadataUpdateStruct',
            $result->userUpdateStruct->contentMetadataUpdateStruct,
            'UserUpdate not created correctly.'
        );

        $this->assertEquals(
            1,
            $result->sectionId,
            'sectionId not created correctly'
        );

        $this->assertEquals(
            'eng-US',
            $result->userUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        $this->assertEquals(
            'remoteId123456',
            $result->userUpdateStruct->contentMetadataUpdateStruct->remoteId,
            'remoteId not created correctly'
        );

        $this->assertEquals(
            'nospam@ez.no',
            $result->userUpdateStruct->email,
            'email not created correctly'
        );

        $this->assertEquals(
            'somePassword',
            $result->userUpdateStruct->password,
            'password not created correctly'
        );

        $this->assertEquals(
            true,
            $result->userUpdateStruct->enabled,
            'enabled not created correctly'
        );

        foreach ( $result->userUpdateStruct->contentUpdateStruct->fields as $field )
        {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserUpdate parser throwing exception on missing Section href
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for Section element in UserUpdate.
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
                        'fieldDefinitionIdentifier' => 'first_name',
                        'fieldValue' => array()
                    )
                )
            ),
            'email' => 'nospam@ez.no',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14'
        );

        $userUpdate = $this->getUserUpdate();
        $userUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserUpdate parser throwing exception on invalid fields data
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'fields' element for UserUpdate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1'
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(),
            'email' => 'nospam@ez.no',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14'
        );

        $userUpdate = $this->getUserUpdate();
        $userUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserUpdate parser throwing exception on missing field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserUpdate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1'
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array()
                    )
                )
            ),
            'email' => 'nospam@ez.no',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14'
        );

        $userUpdate = $this->getUserUpdate();
        $userUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserUpdate parser throwing exception on missing field value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'first_name' identifier in UserUpdate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1'
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'first_name',
                    )
                )
            ),
            'email' => 'nospam@ez.no',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14'
        );

        $userUpdate = $this->getUserUpdate();
        $userUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the UserUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserUpdate
     */
    protected function getUserUpdate()
    {
        return new UserUpdate(
            $this->getUrlHandler(),
            $this->getUserServiceMock(),
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock(),
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

        $fieldTypeParserMock->expects( $this->any() )
            ->method( 'parseFieldValue' )
            ->with( 14, 'first_name', array() )
            ->will( $this->returnValue( 'foo' ) );

        return $fieldTypeParserMock;
    }

    /**
     * Get the user service mock object
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    protected function getUserServiceMock()
    {
        $userServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\UserService',
            array(),
            array(),
            '',
            false
        );

        $userServiceMock->expects( $this->any() )
            ->method( 'newUserUpdateStruct' )
            ->will(
                $this->returnValue( new UserUpdateStruct() )
            );

        return $userServiceMock;
    }

    /**
     * Get the Content service mock object
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

        $contentServiceMock->expects( $this->any() )
            ->method( 'newContentUpdateStruct' )
            ->will(
                $this->returnValue( new ContentUpdateStruct() )
            );

        $contentServiceMock->expects( $this->any() )
            ->method( 'newContentMetadataUpdateStruct' )
            ->will(
                $this->returnValue( new ContentMetadataUpdateStruct() )
            );

        return $contentServiceMock;
    }
}
