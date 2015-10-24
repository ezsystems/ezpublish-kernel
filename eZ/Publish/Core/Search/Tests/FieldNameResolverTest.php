<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Tests;

use ArrayObject;

/**
 * Test case for FieldNameResolver.
 *
 * @covers \eZ\Publish\Core\Search\Common\FieldNameResolver
 */
class FieldNameResolverTest extends TestCase
{
    public function testGetFieldNamesReturnsEmptyArray()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier_1' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ),
                        ),
                        'content_type_identifier_2' => array(
                            'field_definition_identifier_2' => array(
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ),
                        ),
                    )
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
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier_1' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ),
                        ),
                        'content_type_identifier_2' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ),
                            'field_definition_identifier_2' => array(
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ),
                        ),
                    )
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_1',
                'field_definition_identifier_1',
                'field_type_identifier_1',
                null
            )
            ->will($this->returnValue('index_field_name_1'));

        $mockedFieldNameResolver
            ->expects($this->at(2))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                null
            )
            ->will($this->returnValue('index_field_name_2'));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            array(
                'index_field_name_1',
                'index_field_name_2',
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier_1' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ),
                        ),
                        'content_type_identifier_2' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ),
                            'field_definition_identifier_2' => array(
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ),
                        ),
                    )
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_1',
                'field_definition_identifier_1',
                'field_type_identifier_1',
                'field_name'
            )
            ->will($this->returnValue('index_field_name_1'));

        $mockedFieldNameResolver
            ->expects($this->at(2))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                'field_name'
            )
            ->will($this->returnValue('index_field_name_2'));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            null,
            'field_name'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            array(
                'index_field_name_1',
                'index_field_name_2',
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier_1' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ),
                        ),
                        'content_type_identifier_2' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ),
                            'field_definition_identifier_2' => array(
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ),
                        ),
                    )
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                null
            )
            ->will($this->returnValue('index_field_name_1'));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            'field_type_identifier_2',
            null
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            array(
                'index_field_name_1',
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedAndNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $criterionMock = $this->getCriterionMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier_1' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_1',
                                'field_type_identifier' => 'field_type_identifier_1',
                            ),
                        ),
                        'content_type_identifier_2' => array(
                            'field_definition_identifier_1' => array(
                                'field_definition_id' => 'field_definition_id_2',
                                'field_type_identifier' => 'field_type_identifier_2',
                            ),
                            'field_definition_identifier_2' => array(
                                'field_definition_id' => 'field_definition_id_3',
                                'field_type_identifier' => 'field_type_identifier_3',
                            ),
                        ),
                    )
                )
            );

        $mockedFieldNameResolver
            ->expects($this->at(1))
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'
                ),
                'content_type_identifier_2',
                'field_definition_identifier_1',
                'field_type_identifier_2',
                'field_name'
            )
            ->will($this->returnValue('index_field_name_1'));

        $fieldNames = $mockedFieldNameResolver->getFieldNames(
            $criterionMock,
            'field_definition_identifier_1',
            'field_type_identifier_2',
            'field_name'
        );

        $this->assertInternalType('array', $fieldNames);
        $this->assertEquals(
            array(
                'index_field_name_1',
            ),
            $fieldNames
        );
    }

    public function testGetSortFieldName()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier' => array(
                            'field_definition_identifier' => array(
                                'field_definition_id' => 'field_definition_id',
                                'field_type_identifier' => 'field_type_identifier',
                            ),
                        ),
                    )
                )
            );

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getIndexFieldName')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\SortClause'
                ),
                'content_type_identifier',
                'field_definition_identifier',
                'field_type_identifier',
                'field_name'
            )
            ->will($this->returnValue('index_field_name'));

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
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap', 'getIndexFieldName'));
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldNameResolver
            ->expects($this->once())
            ->method('getSearchableFieldMap')
            ->will(
                $this->returnValue(
                    array(
                        'content_type_identifier' => array(
                            'field_definition_identifier' => array(
                                'field_definition_id' => 'field_definition_id',
                                'field_type_identifier' => 'field_type_identifier',
                            ),
                        ),
                    )
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
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));

        $customFieldMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\CustomFieldInterface'
        );
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

        $this->assertEquals('custom_field_name', $customFieldName);
    }

    public function testGetIndexFieldNameNamedField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
                )
            );

        $indexFieldType->expects($this->never())->method('getDefaultField');

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
                $this->isInstanceOf('eZ\\Publish\\SPI\\Search\\FieldType')
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

        $this->assertEquals('generated_typed_field_name', $fieldName);
    }

    public function testGetIndexFieldNameDefaultMatchField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
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
                $this->isInstanceOf('eZ\\Publish\\SPI\\Search\\FieldType')
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

        $this->assertEquals('generated_typed_field_name', $fieldName);
    }

    public function testGetIndexFieldNameDefaultSortField()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
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
                $this->isInstanceOf('eZ\\Publish\\SPI\\Search\\FieldType')
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

        $this->assertEquals('generated_typed_field_name', $fieldName);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameDefaultMatchFieldThrowsRuntimeException()
    {
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
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
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
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
        $mockedFieldNameResolver = $this->getMockedFieldNameResolver(array('getSearchableFieldMap'));
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
                    array(
                        'field_name' => $searchFieldTypeMock,
                    )
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
     * @return \eZ\Publish\Core\Search\Common\FieldNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedFieldNameResolver(array $methods = array())
    {
        $fieldNameResolver = $this
            ->getMockBuilder(
                'eZ\\Publish\\Core\\Search\\Common\\FieldNameResolver'
            )
            ->setConstructorArgs(
                array(
                    $this->getFieldRegistryMock(),
                    $this->getContentTypeHandlerMock(),
                    $this->getFieldNameGeneratorMock(),
                )
            )
            ->setMethods($methods)
            ->getMock();

        return $fieldNameResolver;
    }

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldRegistryMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldRegistryMock()
    {
        if (!isset($this->fieldRegistryMock)) {
            $this->fieldRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Search\\Common\\FieldRegistry'
            );
        }

        return $this->fieldRegistryMock;
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\Indexable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIndexFieldTypeMock()
    {
        return $this->getMock(
            'eZ\\Publish\\SPI\\FieldType\\Indexable'
        );
    }

    /**
     * @return \eZ\Publish\SPI\Search\FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSearchFieldTypeMock()
    {
        return $this->getMock(
            'eZ\\Publish\\SPI\\Search\\FieldType'
        );
    }

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeHandlerMock;

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentTypeHandlerMock()
    {
        if (!isset($this->contentTypeHandlerMock)) {
            $this->contentTypeHandlerMock = $this->getMock(
                'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler'
            );
        }

        return $this->contentTypeHandlerMock;
    }

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldNameGeneratorMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldNameGeneratorMock()
    {
        if (!isset($this->fieldNameGeneratorMock)) {
            $this->fieldNameGeneratorMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Search\\Common\\FieldNameGenerator')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->fieldNameGeneratorMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCriterionMock()
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSortClauseMock()
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\SortClause')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
