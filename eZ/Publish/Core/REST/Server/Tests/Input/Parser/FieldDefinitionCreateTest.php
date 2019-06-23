<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;

/**
 * @todo Test with fieldSettings and validatorConfiguration when specified
 */
class FieldDefinitionCreateTest extends BaseTest
{
    /**
     * Tests the FieldDefinitionCreate parser.
     */
    public function testParse()
    {
        $inputArray = $this->getInputArray();

        $fieldDefinitionCreate = $this->getParser();
        $result = $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            FieldDefinitionCreateStruct::class,
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

        $this->assertTrue(
            $result->isTranslatable,
            'isTranslatable not created correctly'
        );

        $this->assertTrue(
            $result->isRequired,
            'isRequired not created correctly'
        );

        $this->assertTrue(
            $result->isInfoCollector,
            'isInfoCollector not created correctly'
        );

        $this->assertTrue(
            $result->isSearchable,
            'isSearchable not created correctly'
        );

        $this->assertEquals(
            'New title',
            $result->defaultValue,
            'defaultValue not created correctly'
        );

        $this->assertEquals(
            ['eng-US' => 'Title'],
            $result->names,
            'names not created correctly'
        );

        $this->assertEquals(
            ['eng-US' => 'This is the title'],
            $result->descriptions,
            'descriptions not created correctly'
        );

        $this->assertEquals(
            ['textRows' => 24],
            $result->fieldSettings,
            'fieldSettings not created correctly'
        );

        $this->assertEquals(
            [
                'StringLengthValidator' => [
                    'minStringLength' => 12,
                    'maxStringLength' => 24,
                ],
            ],
            $result->validatorConfiguration,
            'validatorConfiguration not created correctly'
        );
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = $this->getInputArray();
        unset($inputArray['identifier']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on missing fieldType.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'fieldType' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnMissingFieldType()
    {
        $inputArray = $this->getInputArray();
        unset($inputArray['fieldType']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on invalid names.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'names' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $inputArray = $this->getInputArray();
        unset($inputArray['names']['value']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test FieldDefinitionCreate parser throwing exception on invalid descriptions.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid 'descriptions' element for FieldDefinitionCreate.
     */
    public function testParseExceptionOnInvalidDescriptions()
    {
        $inputArray = $this->getInputArray();
        unset($inputArray['descriptions']['value']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the FieldDefinitionCreate parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\FieldDefinitionCreate
     */
    protected function internalGetParser()
    {
        return new FieldDefinitionCreate(
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the FieldTypeParser mock object.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected function getFieldTypeParserMock()
    {
        $fieldTypeParserMock = $this->createMock(FieldTypeParser::class);

        $fieldTypeParserMock->expects($this->any())
            ->method('parseValue')
            ->will($this->returnValue('New title'));

        $fieldTypeParserMock->expects($this->any())
            ->method('parseFieldSettings')
            ->will($this->returnValue(['textRows' => 24]));

        $fieldTypeParserMock->expects($this->any())
            ->method('parseValidatorConfiguration')
            ->will(
                $this->returnValue(
                    [
                        'StringLengthValidator' => [
                            'minStringLength' => 12,
                            'maxStringLength' => 24,
                        ],
                    ]
                )
            );

        return $fieldTypeParserMock;
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
            ->method('newFieldDefinitionCreateStruct')
            ->with($this->equalTo('title'), $this->equalTo('ezstring'))
            ->will(
                $this->returnValue(
                    new FieldDefinitionCreateStruct(
                        [
                            'identifier' => 'title',
                            'fieldTypeIdentifier' => 'ezstring',
                        ]
                    )
                )
            );

        return $contentTypeServiceMock;
    }

    /**
     * Returns the array under test.
     *
     * @return array
     */
    protected function getInputArray()
    {
        return [
            'identifier' => 'title',
            'fieldType' => 'ezstring',
            'fieldGroup' => 'content',
            'position' => '1',
            'isTranslatable' => 'true',
            'isRequired' => 'true',
            'isInfoCollector' => 'true',
            'isSearchable' => 'true',
            'defaultValue' => 'New title',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'Title',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'This is the title',
                    ],
                ],
            ],
            // Note that ezstring does not support settings, but that is irrelevant for the test
            'fieldSettings' => [
                'textRows' => 24,
            ],
            'validatorConfiguration' => [
                'StringLengthValidator' => [
                    'minStringLength' => '12',
                    'maxStringLength' => '24',
                ],
            ],
        ];
    }
}
