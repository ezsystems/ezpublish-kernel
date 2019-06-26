<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Tests;

use ArrayObject;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion as APICriterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause as APISortClause;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\SPI\Search\FieldType as SPIFieldType;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPIContentTypeHandler;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;

/**
 * Test case for FieldNameResolver.
 *
 * @covers \eZ\Publish\Core\Search\Common\FieldNameResolver
 */
class FieldNameResolverTest extends TestCase
{
    public function testGetFieldNamesReturnsEmptyArray()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier_1' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ],
                        ],
                        'content_type_identifier_2' => [
                            'field_definition_identifier_2' => [
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ],
                        ],
                    ]
                )
            );

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            'field_type_identifier_2',
            'field_name'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEmpty($fieldNames);
    }

    public function testGetFieldNames()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier_1' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ],
                        ],
                        'content_type_identifier_2' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ],
                            'field_definition_identifier_2' => [
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ],
                        ],
                    ]
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_1',
                'field_definition_identifier_1',
                'field_type_identifier_1',
                null
            )
            ->will($this->returnValue(['index_field_name_1' => null]));

        $mockedFieldNameResolver
            ->expects($this->at(2))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                null
            )
            ->will($this->returnValue(['index_field_name_2' => null]));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            [
                'index_field_name_1',
                'index_field_name_2',
            ],
            $fieldNames
        );
    }

    public function testGetFieldNamesWithNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier_1' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ],
                        ],
                        'content_type_identifier_2' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ],
                            'field_definition_identifier_2' => [
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ],
                        ],
                    ]
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_1',
                'field_definition_identifier_1',
                'field_type_identifier_1',
                'field_name'
            )
            ->will($this->returnValue(['index_field_name_1' => null]));

        $mockedFieldNameResolver
            ->expects($this->at(2))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                'field_name'
            )
            ->will($this->returnValue(['index_field_name_2' => null]));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            null,
            'field_name'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            [
                'index_field_name_1',
                'index_field_name_2',
            ],
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier_1' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ],
                        ],
                        'content_type_identifier_2' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ],
                            'field_definition_identifier_2' => [
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ],
                        ],
                    ]
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                null
            )
            ->will($this->returnValue(['index_field_name_1' => null]));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            'field_type_identifier_2',
            null
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            [
                'index_field_name_1',
            ],
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedAndNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier_1' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ],
                        ],
                        'content_type_identifier_2' => [
                            'field_definition_identifier_1' => [
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ],
                            'field_definition_identifier_2' => [
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ],
                        ],
                    ]
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APICriterion::class
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                'field_name'
            )
            ->will($this->returnValue(['index_field_name_1' => null]));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            'field_type_identifier_2',
            'field_name'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            [
                'index_field_name_1',
            ],
            $fieldNames
        );
    }

    public function testGetSortFieldName()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier' => [
                            'field_definition_identifier' => [
                                'field_definition_id' => 'field_definition_id',
                                'field_type_identifier' => 'field_type_identifier',
                            ],
                        ],
                    ]
                )
            );

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    APISortClause::class
                ),
                'content_type_identifier',
                'field_definition_identifier',
                'field_type_identifier',
                'field_name'
            )
            ->will($this->returnValue(['index_field_name' => null]));

        $fieldName = $mockedFieldNameResolver->getSortFieldName(
            $sortClauseMock,
            'content_type_identifier',
            'field_definition_identifier',
            'field_name'
        );

        $this->assertEquals('index_field_name', $fieldName);
    }

    public function testGetSortFieldNameReturnsNull()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap', 'getIndexFieldName']);
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    [
                        'content_type_identifier' => [
                            'field_definition_identifier' => [
                                'field_definition_id' => 'field_definition_id',
                                'field_type_identifier' => 'field_type_identifier',
                            ],
                        ],
                    ]
                )
            );

        $fieldName = $mockedFieldNameResolver->getSortFieldName(
            $sortClauseMock,
            'non_existent_content_type_identifier',
            'non_existent_field_definition_identifier',
            'field_name'
        );

        $this->assertNull($fieldName);
    }

    public function testGetIndexFieldNameCustomField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);

        $customFieldMock = $this->createMock(CustomFieldInterface::class);
        $customFieldMock
            ->expects($this->once())
            ->method('getCustomField')
            ->with(
                'content_type_identifier',
                'field_definition_identifier'
            )
            ->will(
                $this->returnValue('custom_field_name')
            );

        $customFieldName = $mockedFieldNameResolver->getIndexFieldName(
            $customFieldMock,
            'content_type_identifier',
            'field_definition_identifier',
            'dummy',
            'dummy',
            false
        );

        $this->assertEquals('custom_field_name', key($customFieldName));
    }

    public function testGetIndexFieldNameNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $indexFieldType->expects($this->never())->method('getDefaultSortField');

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getName')
            ->with(
                'field_name',
                'field_definition_identifier',
                'content_type_identifier'
            )
            ->will(
                $this->returnValue('generated_field_name')
            );

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getTypedName')
            ->with(
                'generated_field_name',
                $this->isInstanceOf(SPIFieldType::class)
            )
            ->will(
                $this->returnValue('generated_typed_field_name')
            );

        $fieldName = $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            'field_name',
            true
        );

        $this->assertEquals('generated_typed_field_name', key($fieldName));
    }

    public function testGetIndexFieldNameDefaultMatchField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getDefaultMatchField')
            ->will(
                $this->returnValue('field_name')
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getName')
            ->with(
                'field_name',
                'field_definition_identifier',
                'content_type_identifier'
            )
            ->will(
                $this->returnValue('generated_field_name')
            );

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getTypedName')
            ->with(
                'generated_field_name',
                $this->isInstanceOf(SPIFieldType::class)
            )
            ->will(
                $this->returnValue('generated_typed_field_name')
            );

        $fieldName = $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            null,
            false
        );

        $this->assertEquals('generated_typed_field_name', key($fieldName));
    }

    public function testGetIndexFieldNameDefaultSortField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getDefaultSortField')
            ->will(
                $this->returnValue('field_name')
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getName')
            ->with(
                'field_name',
                'field_definition_identifier',
                'content_type_identifier'
            )
            ->will(
                $this->returnValue('generated_field_name')
            );

        $this->fieldNameGeneratorMock
            ->expects($this->once())
            ->method('getTypedName')
            ->with(
                'generated_field_name',
                $this->isInstanceOf(SPIFieldType::class)
            )
            ->will(
                $this->returnValue('generated_typed_field_name')
            );

        $fieldName = $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            null,
            true
        );

        $this->assertEquals('generated_typed_field_name', key($fieldName));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameDefaultMatchFieldThrowsRuntimeException()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getDefaultMatchField')
            ->will(
                $this->returnValue('non_existent_field_name')
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            null,
            false
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameDefaultSortFieldThrowsRuntimeException()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(['getSearchableFieldMap']);
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getDefaultSortField')
            ->will(
                $this->returnValue('non_existent_field_name')
            );

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            null,
            true
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameNamedFieldThrowsRuntimeException()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(
            ['getSortFieldName', 'getSearchableFieldMap', 'getFieldNames', 'getFieldTypes', 'getSortFieldName']
        );
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects($this->once())
            ->method('getType')
            ->with('field_type_identifier')
            ->will(
                $this->returnValue($indexFieldType)
            );

        $indexFieldType->expects($this->never())->method('getDefaultField');

        $indexFieldType
            ->expects($this->once())
            ->method('getIndexDefinition')
            ->will(
                $this->returnValue(
                    [
                        'field_name' => $searchFieldTypeMock,
                    ]
                )
            );

        $mockedFieldNameResolver->getIndexFieldName(
            new ArrayObject(),
            'content_type_identifier',
            'field_definition_identifier',
            'field_type_identifier',
            'non_existent_field_name',
            false
        );
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Search\Common\FieldNameResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockedFieldNameResolver(array $methods = [])
    {
        $fieldNameResolver = $this
            ->getMockBuilder(FieldNameResolver::class)
            ->setConstructorArgs(
                [
                    $this->getFieldRegistryMock(),
                    $this->getContentTypeHandlerMock(),
                    $this->getFieldNameGeneratorMock(),
                ]
            )
            ->setMethods($methods)
            ->getMock();

        return $fieldNameResolver;
    }

    /** @var \eZ\Publish\Core\Search\Common\FieldRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $fieldRegistryMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldRegistryMock()
    {
        if (!isset($this->fieldRegistryMock)) {
            $this->fieldRegistryMock = $this->createMock(FieldRegistry::class);
        }

        return $this->fieldRegistryMock;
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\Indexable|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getIndexFieldTypeMock()
    {
        return $this->createMock(Indexable::class);
    }

    /**
     * @return \eZ\Publish\SPI\Search\FieldType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSearchFieldTypeMock()
    {
        return $this->createMock(SPIFieldType::class);
    }

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit\Framework\MockObject\MockObject */
    protected $contentTypeHandlerMock;

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContentTypeHandlerMock()
    {
        if (!isset($this->contentTypeHandlerMock)) {
            $this->contentTypeHandlerMock = $this->createMock(SPIContentTypeHandler::class);
        }

        return $this->contentTypeHandlerMock;
    }

    /** @var \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit\Framework\MockObject\MockObject */
    protected $fieldNameGeneratorMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldNameGeneratorMock()
    {
        if (!isset($this->fieldNameGeneratorMock)) {
            $this->fieldNameGeneratorMock = $this->createMock(FieldNameGenerator::class);
        }

        return $this->fieldNameGeneratorMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCriterionMock()
    {
        return $this->createMock(APICriterion::class);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSortClauseMock()
    {
        return $this->createMock(APISortClause::class);
    }
}
