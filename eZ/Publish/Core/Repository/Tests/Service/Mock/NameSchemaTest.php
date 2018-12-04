<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\NameSchemaTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Helper\NameSchemaService;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Mock Test case for NameSchema service.
 */
class NameSchemaTest extends BaseServiceMockTest
{
    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolveUrlAliasSchema
     */
    public function testResolveUrlAliasSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(array('resolve'));

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<urlalias_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveUrlAliasSchema($content, $contentType);

        self::assertEquals(42, $result);
    }

    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolveUrlAliasSchema
     */
    public function testResolveUrlAliasSchemaFallbackToNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(array('resolve'));

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType('<name_schema>', '');

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveUrlAliasSchema($content, $contentType);

        self::assertEquals(42, $result);
    }

    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolveNameSchema
     */
    public function testResolveNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(array('resolve'));

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveNameSchema($content, array(), array(), $contentType);

        self::assertEquals(42, $result);
    }

    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolveNameSchema
     */
    public function testResolveNameSchemaWithFields()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(array('resolve'));

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $fields = array();
        $fields['text3']['cro-HR'] = new TextLineValue('tri');
        $fields['text1']['ger-DE'] = new TextLineValue('ein');
        $fields['text2']['ger-DE'] = new TextLineValue('zwei');
        $fields['text3']['ger-DE'] = new TextLineValue('drei');
        $mergedFields = $fields;
        $mergedFields['text1']['cro-HR'] = new TextLineValue('jedan');
        $mergedFields['text2']['cro-HR'] = new TextLineValue('dva');
        $mergedFields['text1']['eng-GB'] = new TextLineValue('one');
        $mergedFields['text2']['eng-GB'] = new TextLineValue('two');
        $mergedFields['text3']['eng-GB'] = new TextLineValue('');
        $languages = array('eng-GB', 'cro-HR', 'ger-DE');

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($mergedFields),
            $this->equalTo($languages)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveNameSchema($content, $fields, $languages, $contentType);

        self::assertEquals(42, $result);
    }

    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService::resolve method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolve
     * @dataProvider \eZ\Publish\Core\Repository\Tests\Service\Mock\NameSchemaTest::resolveDataProvider
     * @param string[] $schemaIdentifiers
     * @param string $nameSchema
     * @param string[] $languageFieldValues field value translations
     * @param string[] $fieldTitles [language => [field_identifier => title]]
     * @param array $settings NameSchemaService settings
     */
    public function testResolve(
        array $schemaIdentifiers,
        $nameSchema,
        $languageFieldValues,
        $fieldTitles,
        $settings = []
    ) {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['getFieldTitles'], $settings);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $index = 0;
        foreach ($languageFieldValues as $languageCode => $fieldValue) {
            $serviceMock->expects(
                $this->at($index++)
            )->method(
                'getFieldTitles'
            )->with(
                $schemaIdentifiers,
                $contentType,
                $content->fields,
                $languageCode
            )->will(
                $this->returnValue($fieldTitles[$languageCode])
            );
        }

        $result = $serviceMock->resolve($nameSchema, $contentType, $content->fields, $content->versionInfo->languageCodes);

        self::assertEquals($languageFieldValues, $result);
    }

    /**
     * Data provider for the @see testResolve method.
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            [
                ['text1'],
                '<text1>',
                [
                    'eng-GB' => 'one',
                    'cro-HR' => 'jedan',
                ],
                [
                    'eng-GB' => ['text1' => 'one'],
                    'cro-HR' => ['text1' => 'jedan'],
                ],
            ],
            [
                ['text2'],
                '<text2>',
                [
                    'eng-GB' => 'two',
                    'cro-HR' => 'dva',
                ],
                [
                    'eng-GB' => ['text2' => 'two'],
                    'cro-HR' => ['text2' => 'dva'],
                ],
            ],
            [
                ['text1', 'text2'],
                'Hello, <text1> and <text2> and then goodbye and hello again',
                [
                    'eng-GB' => 'Hello, one and two and then goodbye...',
                    'cro-HR' => 'Hello, jedan and dva and then goodb...',
                ],
                [
                    'eng-GB' => ['text1' => 'one', 'text2' => 'two'],
                    'cro-HR' => ['text1' => 'jedan', 'text2' => 'dva'],
                ],
                [
                    'limit' => 38,
                    'sequence' => '...',
                ],
            ],
        ];
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected function getFields()
    {
        return array(
            new Field(
                array(
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('one'),
                )
            ),
            new Field(
                array(
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('two'),
                )
            ),
            new Field(
                array(
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                )
            ),
            new Field(
                array(
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('jedan'),
                )
            ),
            new Field(
                array(
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('dva'),
                )
            ),
            new Field(
                array(
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                )
            ),
        );
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    protected function getFieldDefinitions()
    {
        return array(
            new FieldDefinition(
                array(
                    'id' => '1',
                    'identifier' => 'text1',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
            new FieldDefinition(
                array(
                    'id' => '2',
                    'identifier' => 'text2',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
            new FieldDefinition(
                array(
                    'id' => '3',
                    'identifier' => 'text3',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );
    }

    /**
     * Build Content Object stub for testing purpose.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function buildTestContentObject()
    {
        return new Content(
            [
                'internalFields' => $this->getFields(),
                'versionInfo' => new VersionInfo(
                    [
                        'languageCodes' => ['eng-GB', 'cro-HR'],
                    ]
                ),
            ]
        );
    }

    /**
     * Build ContentType stub for testing purpose.
     *
     * @param string $nameSchema
     * @param string $urlAliasSchema
     *
     * @return \eZ\Publish\Core\Repository\Values\ContentType\ContentType
     */
    protected function buildTestContentType($nameSchema = '<name_schema>', $urlAliasSchema = '<urlalias_schema>')
    {
        return new ContentType(
            [
                'nameSchema' => $nameSchema,
                'urlAliasSchema' => $urlAliasSchema,
                'fieldDefinitions' => $this->getFieldDefinitions(),
            ]
        );
    }

    /**
     * Returns the content service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()}
     *
     * @param string[] $methods
     * @param array $settings
     *
     * @return \eZ\Publish\Core\Repository\Helper\NameSchemaService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedNameSchemaService(array $methods = null, array $settings = [])
    {
        return $this->getMockBuilder(NameSchemaService::class)
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->getPersistenceMock()->contentTypeHandler(),
                    $this->getContentTypeDomainMapperMock(),
                    $this->getNameableFieldTypeRegistryMock(),
                    $settings,
                ]
            )
            ->getMock();
    }
}
