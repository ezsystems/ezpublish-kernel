<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\NameSchemaBase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Test case for NameSchema service.
 */
abstract class NameSchemaBase extends BaseServiceTest
{
    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolve
     * @dataProvider providerForTestResolve
     */
    public function testResolve($nameSchema, $expectedName)
    {
        $service = $this->repository->getNameSchemaService();

        list($content, $contentType) = $this->buildTestObjects();

        $name = $service->resolve(
            $nameSchema,
            $contentType,
            $content->fields,
            $content->versionInfo->languageCodes
        );

        self::assertEquals($expectedName, $name);
    }

    /**
     * Test eZ\Publish\Core\Repository\Helper\NameSchemaService method.
     *
     * @covers \eZ\Publish\Core\Repository\Helper\NameSchemaService::resolve
     */
    public function testResolveWithSettings()
    {
        $service = $this->repository->getNameSchemaService();

        $this->setConfiguration(
            $service,
            [
                'limit' => 38,
                'sequence' => '...',
            ]
        );

        list($content, $contentType) = $this->buildTestObjects();

        $name = $service->resolve(
            'Hello, <text1> and <text2> and then goodbye and hello again',
            $contentType,
            $content->fields,
            $content->versionInfo->languageCodes
        );

        self::assertEquals(
            [
                'eng-GB' => 'Hello, one and two and then goodbye...',
                'cro-HR' => 'Hello, jedan and dva and then goodb...',
            ],
            $name
        );
    }

    public function providerForTestResolve()
    {
        return [
            [
                '<text1>',
                [
                    'eng-GB' => 'one',
                    'cro-HR' => 'jedan',
                ],
            ],
            [
                '<text1> <text2>',
                [
                    'eng-GB' => 'one two',
                    'cro-HR' => 'jedan dva',
                ],
            ],
            [
                'Hello <text1>',
                [
                    'eng-GB' => 'Hello one',
                    'cro-HR' => 'Hello jedan',
                ],
            ],
            [
                'Hello, <text1> and <text2> and then goodbye',
                [
                    'eng-GB' => 'Hello, one and two and then goodbye',
                    'cro-HR' => 'Hello, jedan and dva and then goodbye',
                ],
            ],
            [
                '<text1|text2>',
                [
                    'eng-GB' => 'one',
                    'cro-HR' => 'jedan',
                ],
            ],
            [
                '<text2|text1>',
                [
                    'eng-GB' => 'two',
                    'cro-HR' => 'dva',
                ],
            ],
            [
                '<text3|text1>',
                [
                    'eng-GB' => 'one',
                    'cro-HR' => 'jedan',
                ],
            ],
            [
                '<(<text1> <text2>)>',
                [
                    'eng-GB' => 'one two',
                    'cro-HR' => 'jedan dva',
                ],
            ],
            [
                '<(<text3|text2>)>',
                [
                    'eng-GB' => 'two',
                    'cro-HR' => 'dva',
                ],
            ],
            [
                '<text3|(<text3|text2>)>',
                [
                    'eng-GB' => 'two',
                    'cro-HR' => 'dva',
                ],
            ],
            [
                '<text3|(Hello <text2> and <text1>!)>',
                [
                    'eng-GB' => 'Hello two and one!',
                    'cro-HR' => 'Hello dva and jedan!',
                ],
            ],
            [
                '<text3|(Hello <text3> and <text1>)|text2>!',
                [
                    'eng-GB' => 'Hello  and one!',
                    'cro-HR' => 'Hello  and jedan!',
                ],
            ],
            [
                '<text3|(Hello <text3|text2> and <text1>)|text2>!',
                [
                    'eng-GB' => 'Hello two and one!',
                    'cro-HR' => 'Hello dva and jedan!',
                ],
            ],
        ];
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected function getFields()
    {
        return [
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('one'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('two'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('jedan'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('dva'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                ]
            ),
        ];
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    protected function getFieldDefinitions()
    {
        return [
            new FieldDefinition(
                [
                    'id' => '1',
                    'identifier' => 'text1',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => '2',
                    'identifier' => 'text2',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => '3',
                    'identifier' => 'text3',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
        ];
    }

    /**
     * Builds stubbed content for testing purpose.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function buildTestObjects($nameSchema = '<name_schema>', $urlAliasSchema = '<urlalias_schema>')
    {
        $contentType = new ContentType(
            [
                'nameSchema' => $nameSchema,
                'urlAliasSchema' => $urlAliasSchema,
                'fieldDefinitions' => $this->getFieldDefinitions(),
            ]
        );
        $content = new Content(
            [
                'internalFields' => $this->getFields(),
                'versionInfo' => new VersionInfo(
                    [
                        'languageCodes' => ['eng-GB', 'cro-HR'],
                    ]
                ),
            ]
        );

        return [$content, $contentType];
    }

    /**
     * @param object $service
     * @param array $configuration
     */
    protected function setConfiguration($service, array $configuration)
    {
        $refObject = new \ReflectionObject($service);
        $refProperty = $refObject->getProperty('settings');
        $refProperty->setAccessible(true);
        $refProperty->setValue(
            $service,
            $configuration
        );
    }
}
