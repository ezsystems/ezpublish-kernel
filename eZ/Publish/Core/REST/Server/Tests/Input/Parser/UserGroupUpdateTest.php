<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\UserGroupUpdate;

class UserGroupUpdateTest extends BaseTest
{
    /**
     * Tests the UserGroupUpdate parser
     */
    public function testParse()
    {
        $this->markTestSkipped( '@todo Skipped due to InMemory implementation of Location value object does not implement getting of contentId virtual property' );
        $inputArray = array(
            'mainLanguageCode' => 'eng-US',
            'Section' => array(
                '_href' => '/content/sections/1'
            ),
            'remoteId' => 'remoteId123456',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/user/groups/1/5'
        );

        $userGroupUpdate = $this->getUserGroupUpdate();
        $result = $userGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestContentUpdateStruct',
            $result,
            'UserGroupUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct',
            $result->userGroupUpdateStruct->contentUpdateStruct,
            'UserGroupUpdate not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentMetadataUpdateStruct',
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

        foreach ( $result->userGroupUpdateStruct->contentUpdateStruct->fields as $field )
        {
            $this->assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing Section href
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
                        'fieldValue' => array()
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/user/groups/1/5'
        );

        $userGroupUpdate = $this->getUserGroupUpdateWithSimpleFieldTypeMock();
        $userGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupUpdate parser throwing exception on invalid fields data
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'fields' element for UserGroupUpdate.
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
            '__url' => '/user/groups/1/5'
        );

        $userGroupUpdate = $this->getUserGroupUpdateWithSimpleFieldTypeMock();
        $userGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for UserGroupUpdate.
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
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/user/groups/1/5'
        );

        $userGroupUpdate = $this->getUserGroupUpdateWithSimpleFieldTypeMock();
        $userGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'name' identifier in UserGroupUpdate.
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
                        'fieldDefinitionIdentifier' => 'name',
                    ),
                    array(
                        'fieldDefinitionIdentifier' => 'description',
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/user/groups/1/5'
        );

        $userGroupUpdate = $this->getUserGroupUpdateWithSimpleFieldTypeMock();
        $userGroupUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the UserGroupUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupUpdate
     */
    protected function getUserGroupUpdate()
    {
        return new UserGroupUpdate(
            $this->getUrlHandler(),
            $this->getRepository()->getUserService(),
            $this->getRepository()->getContentService(),
            $this->getRepository()->getLocationService(),
            $this->getFieldTypeParserMock()
        );
    }

    /**
     * Returns the UserGroupUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\UserGroupUpdate
     */
    protected function getUserGroupUpdateWithSimpleFieldTypeMock()
    {
        return new UserGroupUpdate(
            $this->getUrlHandler(),
            $this->getRepository()->getUserService(),
            $this->getRepository()->getContentService(),
            $this->getRepository()->getLocationService(),
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
        $fieldTypeParserMock = $this->getSimpleFieldTypeParserMock();

        $fieldTypeParserMock->expects( $this->at( 0 ) )
            ->method( 'parseFieldValue' )
            ->with( 4, 'name', array() )
            ->will( $this->returnValue( 'foo' ) );

        $fieldTypeParserMock->expects( $this->at( 1 ) )
            ->method( 'parseFieldValue' )
            ->with( 4, 'description', array() )
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
