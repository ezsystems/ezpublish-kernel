<?php
/**
 * File containing a ContentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\Values;

class FieldDefinitionTest extends BaseTest
{
    protected $fieldTypeParserMock;

    public function setUp()
    {
        parent::setUp();
        $this->fieldTypeParserMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Common\\Input\\FieldTypeParser',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testParse()
    {
        $fieldDefinitionParser = $this->getParser();

        $inputArray = array(
            '_media-type' => 'application/vnd.ez.api.FieldDefinition+json',
            '_href' => '/content/types/1/fieldDefinitions/292',
            'id' => 292,
            'identifier' => 'tags',
            'fieldType' => 'ezkeyword',
            'fieldGroup' => 'fancy-field-group',
            'position' => 7,
            'isTranslatable' => 'true',
            'isRequired' => 'false',
            'isInfoCollector' => 'false',
            'defaultValue' => array( 'ValueMock' ),
            'fieldSettings' => array( 'SettingsMock' ),
            'validatorConfiguration' => array( 'ValidatorMock' ),
            'isSearchable' => 'false',
            'names' => array(
                'value' => array(
                    0 => array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Tags',
                    ),
                ),
            ),
            'descriptions' => array(
                'value' => array(
                    0 => array(
                        '_languageCode' => 'eng-US',
                        '#text' => 'Sindelfingen',
                    ),
                ),
            )
        );

        $this->fieldTypeParserMock->expects( $this->once() )
            ->method( 'parseValue' )
            ->with(
                $this->equalTo( 'ezkeyword' ),
                $this->equalTo( array( 'ValueMock' ) )
            )
            ->will( $this->returnValue( 'ParsedValueMock' ) );

        $this->fieldTypeParserMock->expects( $this->once() )
            ->method( 'parseFieldSettings' )
            ->with(
                $this->equalTo( 'ezkeyword' ),
                $this->equalTo( array( 'SettingsMock' ) )
            )->will( $this->returnValue( 'ParsedSettingsMock' ) );

        $this->fieldTypeParserMock->expects( $this->once() )
            ->method( 'parseValidatorConfiguration' )
            ->with(
                $this->equalTo( 'ezkeyword' ),
                $this->equalTo( array( 'ValidatorMock' ) )
            )->will( $this->returnValue( 'ParsedValidatorMock' ) );

        $result = $fieldDefinitionParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @dataProvider provideExpectedFieldDefinitionProperties
     * @depends testParse
     */
    public function testParsedProperties( $propertyName, $expectedValue, $parsedFieldDefinition )
    {
        $this->assertEquals(
            $expectedValue,
            $parsedFieldDefinition->$propertyName,
            "Property \${$propertyName} parsed incorrectly."
        );
    }

    public function provideExpectedFieldDefinitionProperties()
    {
        return array(
            array(
                'id',
                '/content/types/1/fieldDefinitions/292',
            ),
            array(
                'identifier',
                'tags',
            ),
            array(
                'fieldGroup',
                'fancy-field-group',
            ),
            array(
                'position',
                7,
            ),
            array(
                'fieldTypeIdentifier',
                'ezkeyword',
            ),
            array(
                'isTranslatable',
                true,
            ),
            array(
                'isRequired',
                false,
            ),
            array(
                'isSearchable',
                false,
            ),
            array(
                'isInfoCollector',
                false,
            ),
            array(
                'defaultValue',
                'ParsedValueMock',
            ),
            array(
                'names',
                array( 'eng-US' => 'Tags' ),
            ),
            array(
                'descriptions',
                array( 'eng-US' => 'Sindelfingen' ),
            )
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\FieldDefinition
     */
    protected function getParser()
    {
        return new Input\Parser\FieldDefinition(
            new ParserTools(),
            $this->fieldTypeParserMock
        );
    }
}
