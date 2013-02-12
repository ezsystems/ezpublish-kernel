<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate;

/**
 * @todo Test with fieldSettings and validatorConfiguration when specified
 */
class FieldDefinitionCreateTest extends BaseTest
{
    /**
     * Tests the FieldDefinitionCreate parser
     */
    public function testParse()
    {
        $inputArray = $this->getInputArray();

        $fieldDefinitionCreate = $this->getFieldDefinitionCreate();
        $result = $fieldDefinitionCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinitionCreateStruct',
            $result,
            'FieldDefinitionCreateStruct not created correctly.'
        );

        $this->assertEquals(
            'title',
            $result->identifier,
            'identifier not created correctly'
        );

        $this->assertEquals(
            'ezstring',
            $result->fieldTypeIdentifier,
            'fieldTypeIdentifier not created correctly'
        );

        $this->assertEquals(
            'content',
            $result->fieldGroup,
            'fieldGroup not created correctly'
        );

        $this->assertEquals(
            1,
            $result->position,
            'position not created correctly'
        );

        $this->assertEquals(
            true,
            $result->isTranslatable,
            'isTranslatable not created correctly'
        );

        $this->assertEquals(
            true,
            $result->isRequired,
            'isRequired not created correctly'
        );

        $this->assertEquals(
            true,
            $result->isInfoCollector,
            'isInfoCollector not created correctly'
        );

        $this->assertEquals(
            true,
            $result->isSearchable,
            'isSearchable not created correctly'
        );

        $this->assertEquals(
            'New title',
            $result->defaultValue,
            'defaultValue not created correctly'
        );

        $this->assertEquals(
            array( 'eng-US' => 'Title' ),
            $result->names,
            'names not created correctly'
        );

        $this->assertEquals(
            array( 'eng-US' => 'This is the title' ),
            $result->descriptions,
            'descriptions not created correctly'
        );
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on missing identifier
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['identifier'] );

        $fieldDefinitionCreate = $this->getFieldDefinitionCreate();
        $fieldDefinitionCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on missing fieldType
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldType' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnMissingFieldType()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['fieldType'] );

        $fieldDefinitionCreate = $this->getFieldDefinitionCreate();
        $fieldDefinitionCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on invalid names
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'names' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['names']['value'] );

        $fieldDefinitionCreate = $this->getFieldDefinitionCreate();
        $fieldDefinitionCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on invalid descriptions
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'descriptions' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnInvalidDescriptions()
    {
        $inputArray = $this->getInputArray();
        unset( $inputArray['descriptions']['value'] );

        $fieldDefinitionCreate = $this->getFieldDefinitionCreate();
        $fieldDefinitionCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the FieldDefinitionCreate parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate
     */
    protected function getFieldDefinitionCreate()
    {
        return new FieldDefinitionCreate(
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
            ->method( 'newFieldDefinitionCreateStruct' )
            ->with( $this->equalTo( 'title' ), $this->equalTo( 'ezstring' ) )
            ->will(
                $this->returnValue(
                    new FieldDefinitionCreateStruct(
                        array(
                            'identifier' => 'title',
                            'fieldTypeIdentifier' => 'ezstring'
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
            'identifier' => 'title',
            'fieldType' => 'ezstring',
            'fieldGroup' => 'content',
            'position' => '1',
            'isTranslatable' => 'true',
            'isRequired' => 'true',
            'isInfoCollector' => 'true',
            'isSearchable' => 'true',
            'defaultValue' => 'New title',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Title'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'This is the title'
                    )
                )
            )
        );
    }
}
