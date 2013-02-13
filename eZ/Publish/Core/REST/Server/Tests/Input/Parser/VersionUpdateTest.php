<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\VersionUpdate;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;

class VersionUpdateTest extends BaseTest
{
    /**
     * Tests the VersionUpdate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'initialLanguageCode' => 'eng-US',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/content/objects/42/versions/1'
        );

        $VersionUpdate = $this->getVersionUpdate();
        $result = $VersionUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct',
            $result,
            'VersionUpdate not created correctly.'
        );

        $this->assertEquals(
            'eng-US',
            $result->initialLanguageCode,
            'initialLanguageCode not created correctly'
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
     * Test VersionUpdate parser throwing exception on invalid fields data
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'fields' element for VersionUpdate.
     */
    public function testParseExceptionOnInvalidFields()
    {
        $inputArray = array(
            'initialLanguageCode' => 'eng-US',
            'fields' => array(),
            '__url' => '/content/objects/42/versions/1'
        );

        $VersionUpdate = $this->getVersionUpdate();
        $VersionUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test VersionUpdate parser throwing exception on missing field definition identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldDefinitionIdentifier' element in field data for VersionUpdate.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier()
    {
        $inputArray = array(
            'initialLanguageCode' => 'eng-US',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldValue' => array()
                    )
                )
            ),
            '__url' => '/content/objects/42/versions/1'
        );

        $VersionUpdate = $this->getVersionUpdate();
        $VersionUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test VersionUpdate parser throwing exception on missing field value
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldValue' element for 'subject' identifier in VersionUpdate.
     */
    public function testParseExceptionOnMissingFieldValue()
    {
        $inputArray = array(
            'initialLanguageCode' => 'eng-US',
            'fields' => array(
                'field' => array(
                    array(
                        'fieldDefinitionIdentifier' => 'subject'
                    )
                )
            ),
            '__url' => '/content/objects/42/versions/1'
        );

        $VersionUpdate = $this->getVersionUpdate();
        $VersionUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the VersionUpdate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\VersionUpdate
     */
    protected function getVersionUpdate()
    {
        return new VersionUpdate(
            $this->getUrlHandler(),
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock()
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
            ->with( 42, 'subject', array() )
            ->will( $this->returnValue( 'foo' ) );

        return $fieldTypeParserMock;
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

        return $contentServiceMock;
    }
}
