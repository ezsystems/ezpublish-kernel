<?php

/**
 * File containing the ContentExtensionIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration tests for ContentExtension templates.
 *
 * Tests ContentExtension in context of site with "fre-FR, eng-US" configured as languages.
 */
class ContentExtensionTest extends FileSystemTwigIntegrationTestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldHelperMock;

    private $fieldDefinitions = [];

    public function getExtensions()
    {
        $this->fieldHelperMock = $this->getMockBuilder('eZ\\Publish\\Core\\Helper\\FieldHelper')
            ->disableOriginalConstructor()->getMock();
        $configResolver = $this->getConfigResolverMock();

        return [
            new ContentExtension(
                $this->getRepositoryMock(),
                new TranslationHelper(
                    $configResolver,
                    $this->getMock('eZ\\Publish\\API\\Repository\\ContentService'),
                    [],
                    $this->getMock('Psr\Log\LoggerInterface')
                ),
                $this->fieldHelperMock
            ),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/content_functions/';
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

    private function getConfigResolverMock()
    {
        $mock = $this->getMock(
            'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface'
        );
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

    protected function getField($isEmpty)
    {
        $field = new Field(['fieldDefIdentifier' => 'testfield', 'value' => null]);

        $this->fieldHelperMock
            ->expects($this->once())
            ->method('isFieldEmpty')
            ->will($this->returnValue($isEmpty));

        return $field;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        $mock = $this->getMock('eZ\\Publish\\API\\Repository\\Repository');

        $mock->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($this->getContentTypeServiceMock()));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentTypeServiceMock()
    {
        $mock = $this->getMock('eZ\\Publish\\API\\Repository\\ContentTypeService');

        $mock->expects($this->any())
            ->method('loadContentType')
            ->will(
                $this->returnCallback(
                    function ($contentTypeId) {
                        return new ContentType(
                            [
                                'identifier' => $contentTypeId,
                                'mainLanguageCode' => 'fre-FR',
                                'fieldDefinitions' => $this->fieldDefinitions[$contentTypeId],
                            ]
                        );
                    }
                )
            );

        return $mock;
    }
}
