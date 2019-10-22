<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldValueDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextLineComparisonResult;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Comparison\Engine\Value\StringValueComparisonEngine;
use eZ\Publish\Core\FieldType\TextLine\Type as TextLineFieldType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Comparison\ComparisonEngineRegistry;
use eZ\Publish\Core\Comparison\FieldRegistry;
use eZ\Publish\Core\Comparison\Engine\FieldType\TextLineComparisonEngine;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\FieldType\TextLine\Comparable as TextLineCompareField;
use eZ\Publish\Core\Repository\ContentComparisonService;
use eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper;
use eZ\Publish\SPI\Comparison\Field\TextLine;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition;

class ComparisonTest extends Base
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $contentHandler;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeHandler;

    /** @var \eZ\Publish\Core\Comparison\FieldRegistry */
    private $fieldRegistry;

    /** @var \eZ\Publish\Core\Comparison\ComparisonEngineRegistry */
    private $compareEngineRegistry;

    /** @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeDomainMapperMock;

    public function setUp(): void
    {
        $this->contentHandler = $this->getPersistenceMockHandler('Content\\Handler');
        $this->contentTypeHandler = $this->getPersistenceMockHandler('Content\\Type\\Handler');

        $this->fieldRegistry = $this->buildFieldRegistry();
        $this->fieldRegistry->registerType('ezstring', new TextLineCompareField());

        $this->compareEngineRegistry = $this->buildCompareEngineRegistry();
        $this->compareEngineRegistry->registerEngine(
            TextLine::class,
            new TextLineComparisonEngine(
                new StringValueComparisonEngine()
            )
        );

        $this->contentTypeDomainMapperMock = $this->buildContentTypeDomainMapperMock();
        $permissionResolverMock = $this->getPermissionResolverMock();

        $permissionResolverMock
            ->method('canUser')
            ->willReturn(true);

        parent::setUp();
    }

    /**
     * @return \eZ\Publish\API\Repository\ContentComparisonService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createCompareService(array $methods = [])
    {
        return $this
            ->getMockBuilder(ContentComparisonService::class)
            ->setMethodsExcept($methods)
            ->setConstructorArgs([
                $this->contentHandler,
                $this->contentTypeHandler,
                $this->fieldRegistry,
                $this->compareEngineRegistry,
                $this->contentTypeDomainMapperMock,
                $this->getPermissionResolverMock(),
            ])
            ->getMock();
    }

    private function buildFieldRegistry(): FieldRegistry
    {
        return new FieldRegistry();
    }

    private function buildCompareEngineRegistry(): ComparisonEngineRegistry
    {
        return new ComparisonEngineRegistry();
    }

    private function buildContentTypeDomainMapperMock()
    {
        return $this
            ->getMockBuilder(ContentTypeDomainMapper::class)
            ->setConstructorArgs([
                $this->contentTypeHandler,
                $this->getPersistenceMockHandler('Content\\Language\\Handler'),
                $this->buildFieldTypeRegistry(),
            ])
            ->getMock();
    }

    private function buildFieldTypeRegistry(): FieldTypeRegistry
    {
        $fieldTypeRegistry = new FieldTypeRegistry();
        $textTypeFieldType = new TextLineFieldType();

        $fieldTypeRegistry->registerFieldType('ezstring', $textTypeFieldType);

        return $fieldTypeRegistry;
    }

    public function testCompareTwoVersions(): void
    {
        $versionOne = $this->getVersionMock(77, 1);
        $versionTwo = $this->getVersionMock(77, 2);

        $contentOne = new Content([
            'fields' => [
                new Field([
                    'id' => 3,
                    'languageCode' => 'eng-GB',
                    'fieldDefinitionId' => 'textDefId',
                    'type' => 'ezstring',
                    'value' => new PersistenceValue([
                        'data' => 'We love the Big Apple',
                    ]),
                ]),
            ],
        ]);

        $contentTwo = new Content([
            'fields' => [
                new Field([
                    'id' => 3,
                    'languageCode' => 'eng-GB',
                    'fieldDefinitionId' => 'textDefId',
                    'type' => 'ezstring',
                    'value' => new PersistenceValue([
                        'data' => 'We love NY',
                    ]),
                ]),
            ],
        ]);

        $this->contentHandler
            ->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                [77, 1],
                [77, 2]
            )->willReturnOnConsecutiveCalls(
                $contentOne,
                $contentTwo
            );

        $this->contentTypeHandler
            ->method('getFieldDefinition')
            ->with('textDefId', Type::STATUS_DEFINED)
            ->willReturn(new SPIFieldDefinition([
                'fieldType' => 'ezstring',
                'identifier' => 'textDefId',
            ]));

        $fieldDefinition = new FieldDefinition();
        $this->contentTypeDomainMapperMock
            ->method('buildFieldDefinitionDomainObject')
            ->willReturn($fieldDefinition);

        $service = $this->createCompareService(['compareVersions']);

        $versionDiff = $service->compareVersions($versionOne, $versionTwo);

        $this->assertInstanceOf(
            VersionDiff::class,
            $versionDiff
        );

        $diffValue = new TextLineComparisonResult([
            new StringDiff('We', DiffStatus::UNCHANGED),
            new StringDiff('love', DiffStatus::UNCHANGED),
            new StringDiff('the', DiffStatus::REMOVED),
            new StringDiff('Big', DiffStatus::REMOVED),
            new StringDiff('Apple', DiffStatus::REMOVED),
            new StringDiff('NY', DiffStatus::ADDED),
        ]);

        $expectedFieldDiff = new FieldValueDiff(
            $fieldDefinition,
            $diffValue,
            true
        );

        $expectedVersionDiff = new VersionDiff([
            'textDefId' => $expectedFieldDiff,
        ]);

        $this->assertEquals(
            $expectedVersionDiff,
            $versionDiff
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getVersionMock(int $id, int $versionNo): VersionInfo
    {
        $versionOne = $this->createMock(VersionInfo::class);

        $versionOne
            ->method('getContentInfo')
            ->willReturn(new ContentInfo(['id' => $id]));

        $versionOne
            ->method('__get')
            ->willReturnMap(
                [
                    ['versionNo', $versionNo],
                    ['initialLanguageCode', 'eng-GB'],
                    ['languageCodes', ['eng-GB', 'eng-US']],
                ]
            );

        return $versionOne;
    }
}
