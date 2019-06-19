<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\FieldRenderingExtension;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\FieldBlockRenderer;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Psr\Log\LoggerInterface;

class FieldRenderingExtensionIntegrationTest extends FileSystemTwigIntegrationTestCase
{
    private $fieldDefinitions = [];

    public function getExtensions()
    {
        $configResolver = $this->getConfigResolverMock();

        $fieldBlockRenderer = new FieldBlockRenderer();
        $fieldBlockRenderer->setBaseTemplate($this->getTemplatePath('base.html.twig'));
        $fieldBlockRenderer->setFieldViewResources(
            [
                [
                    'template' => $this->getTemplatePath('fields_override1.html.twig'),
                    'priority' => 10,
                ],
                [
                    'template' => $this->getTemplatePath('fields_default.html.twig'),
                    'priority' => 0,
                ],
                [
                    'template' => $this->getTemplatePath('fields_override2.html.twig'),
                    'priority' => 20,
                ],
            ]
        );
        $fieldBlockRenderer->setFieldDefinitionViewResources(
            [
                [
                    'template' => $this->getTemplatePath('settings_override1.html.twig'),
                    'priority' => 10,
                ],
                [
                    'template' => $this->getTemplatePath('settings_default.html.twig'),
                    'priority' => 0,
                ],
                [
                    'template' => $this->getTemplatePath('settings_override2.html.twig'),
                    'priority' => 20,
                ],
            ]
        );

        return [
            new FieldRenderingExtension(
                $fieldBlockRenderer,
                $this->createMock(ParameterProviderRegistryInterface::class),
                new TranslationHelper(
                    $configResolver,
                    $this->createMock(ContentService::class),
                    [],
                    $this->createMock(LoggerInterface::class)
                )
            ),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/field_rendering_functions/';
    }

    public function getFieldDefinition($typeIdentifier, $id = null, $settings = [])
    {
        return new FieldDefinition(
            [
                'id' => $id,
                'fieldSettings' => $settings,
                'fieldTypeIdentifier' => $typeIdentifier,
            ]
        );
    }

    /**
     * Creates content with initial/main language being fre-FR.
     *
     * @param string $contentTypeIdentifier
     * @param array $fieldsData
     * @param array $namesData
     *
     * @return Content
     */
    protected function getContent($contentTypeIdentifier, array $fieldsData, array $namesData = [])
    {
        $fields = [];
        foreach ($fieldsData as $fieldTypeIdentifier => $fieldsArray) {
            $fieldsArray = isset($fieldsArray['id']) ? [$fieldsArray] : $fieldsArray;
            foreach ($fieldsArray as $fieldInfo) {
                // Save field definitions in property for mocking purposes
                $this->fieldDefinitions[$contentTypeIdentifier][$fieldInfo['fieldDefIdentifier']] = new FieldDefinition(
                    [
                        'identifier' => $fieldInfo['fieldDefIdentifier'],
                        'id' => $fieldInfo['id'],
                        'fieldTypeIdentifier' => $fieldTypeIdentifier,
                        'names' => isset($fieldInfo['fieldDefNames']) ? $fieldInfo['fieldDefNames'] : [],
                        'descriptions' => isset($fieldInfo['fieldDefDescriptions']) ? $fieldInfo['fieldDefDescriptions'] : [],
                    ]
                );
                unset($fieldInfo['fieldDefNames'], $fieldInfo['fieldDefDescriptions']);
                $fields[] = new Field($fieldInfo);
            }
        }
        $content = new Content(
            [
                'internalFields' => $fields,
                'contentType' => new ContentType([
                    'id' => $contentTypeIdentifier,
                    'identifier' => $contentTypeIdentifier,
                    'mainLanguageCode' => 'fre-FR',
                    'fieldDefinitions' => $this->fieldDefinitions[$contentTypeIdentifier],
                ]),
                'versionInfo' => new VersionInfo(
                    [
                        'versionNo' => 64,
                        'names' => $namesData,
                        'initialLanguageCode' => 'fre-FR',
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                                'mainLanguageCode' => 'fre-FR',
                                // Using as id as we don't really care to test the service here
                                'contentTypeId' => $contentTypeIdentifier,
                            ]
                        ),
                    ]
                ),
            ]
        );

        return $content;
    }

    private function getTemplatePath($tpl)
    {
        return 'templates/' . $tpl;
    }

    private function getConfigResolverMock()
    {
        $mock = $this->createMock(ConfigResolverInterface::class);
        // Signature: ConfigResolverInterface->getParameter( $paramName, $namespace = null, $scope = null )
        $mock->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'languages',
                            null,
                            null,
                            ['fre-FR', 'eng-US'],
                        ],
                    ]
                )
            );

        return $mock;
    }
}
