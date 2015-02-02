<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Elasticsearch\Tests\Content\Search\FieldMapTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Tests\Content\Search;

use eZ\Publish\Core\Persistence\Elasticsearch\Tests\TestCase;
use ArrayObject;

/**
 * Test case for FieldMap
 *
 * @covers \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap
 */
class FieldMapTest extends TestCase
{
    public function testGetFieldNamesReturnsEmptyArray()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $criterionMock = $this->getCriterionMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier_1" => array(
                            "field_definition_identifier_1" => "field_type_identifier_1",
                        ),
                        "content_type_identifier_2" => array(
                            "field_definition_identifier_2" => "field_type_identifier_2",
                        ),
                    )
                )
            );

        $fieldNames = $mockedFieldMap->getFieldNames(
            $criterionMock,
            "field_definition_identifier_1",
            "field_type_identifier_2",
            "field_name"
        );

        $this->assertInternalType( "array", $fieldNames );
        $this->assertEmpty( $fieldNames );
    }

    public function testGetFieldNames()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $criterionMock = $this->getCriterionMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier_1" => array(
                            "field_definition_identifier_1" => "field_type_identifier_1",
                        ),
                        "content_type_identifier_2" => array(
                            "field_definition_identifier_1" => "field_type_identifier_2",
                            "field_definition_identifier_2" => "field_type_identifier_3",
                        ),
                    )
                )
            );

        $mockedFieldMap
            ->expects( $this->at( 1 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_1",
                "field_definition_identifier_1",
                "field_type_identifier_1",
                null
            )
            ->will( $this->returnValue( "index_field_name_1" ) );

        $mockedFieldMap
            ->expects( $this->at( 2 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_2",
                "field_definition_identifier_1",
                "field_type_identifier_2",
                null
            )
            ->will( $this->returnValue( "index_field_name_2" ) );

        $fieldNames = $mockedFieldMap->getFieldNames(
            $criterionMock,
            "field_definition_identifier_1"
        );

        $this->assertInternalType( "array", $fieldNames );
        $this->assertEquals(
            array(
                "index_field_name_1",
                "index_field_name_2",
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithNamedField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $criterionMock = $this->getCriterionMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier_1" => array(
                            "field_definition_identifier_1" => "field_type_identifier_1",
                        ),
                        "content_type_identifier_2" => array(
                            "field_definition_identifier_1" => "field_type_identifier_2",
                            "field_definition_identifier_2" => "field_type_identifier_3",
                        ),
                    )
                )
            );

        $mockedFieldMap
            ->expects( $this->at( 1 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_1",
                "field_definition_identifier_1",
                "field_type_identifier_1",
                "field_name"
            )
            ->will( $this->returnValue( "index_field_name_1" ) );

        $mockedFieldMap
            ->expects( $this->at( 2 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_2",
                "field_definition_identifier_1",
                "field_type_identifier_2",
                "field_name"
            )
            ->will( $this->returnValue( "index_field_name_2" ) );

        $fieldNames = $mockedFieldMap->getFieldNames(
            $criterionMock,
            "field_definition_identifier_1",
            null,
            "field_name"
        );

        $this->assertInternalType( "array", $fieldNames );
        $this->assertEquals(
            array(
                "index_field_name_1",
                "index_field_name_2",
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $criterionMock = $this->getCriterionMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier_1" => array(
                            "field_definition_identifier_1" => "field_type_identifier_1",
                        ),
                        "content_type_identifier_2" => array(
                            "field_definition_identifier_1" => "field_type_identifier_2",
                            "field_definition_identifier_2" => "field_type_identifier_3",
                        ),
                    )
                )
            );

        $mockedFieldMap
            ->expects( $this->at( 1 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_2",
                "field_definition_identifier_1",
                "field_type_identifier_2",
                null
            )
            ->will( $this->returnValue( "index_field_name_1" ) );

        $fieldNames = $mockedFieldMap->getFieldNames(
            $criterionMock,
            "field_definition_identifier_1",
            "field_type_identifier_2",
            null
        );

        $this->assertInternalType( "array", $fieldNames );
        $this->assertEquals(
            array(
                "index_field_name_1",
            ),
            $fieldNames
        );
    }

    public function testGetFieldNamesWithTypedAndNamedField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $criterionMock = $this->getCriterionMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier_1" => array(
                            "field_definition_identifier_1" => "field_type_identifier_1",
                        ),
                        "content_type_identifier_2" => array(
                            "field_definition_identifier_1" => "field_type_identifier_2",
                            "field_definition_identifier_2" => "field_type_identifier_3",
                        ),
                    )
                )
            );

        $mockedFieldMap
            ->expects( $this->at( 1 ) )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion"
                ),
                "content_type_identifier_2",
                "field_definition_identifier_1",
                "field_type_identifier_2",
                "field_name"
            )
            ->will( $this->returnValue( "index_field_name_1" ) );

        $fieldNames = $mockedFieldMap->getFieldNames(
            $criterionMock,
            "field_definition_identifier_1",
            "field_type_identifier_2",
            "field_name"
        );

        $this->assertInternalType( "array", $fieldNames );
        $this->assertEquals(
            array(
                "index_field_name_1",
            ),
            $fieldNames
        );
    }

    public function testGetSortFieldName()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier" => array(
                            "field_definition_identifier" => "field_type_identifier",
                        ),
                    )
                )
            );

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getIndexFieldName" )
            ->with(
                $this->isInstanceOf(
                    "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\SortClause"
                ),
                "content_type_identifier",
                "field_definition_identifier",
                "field_type_identifier",
                "field_name"
            )
            ->will( $this->returnValue( "index_field_name" ) );

        $fieldName = $mockedFieldMap->getSortFieldName(
            $sortClauseMock,
            "content_type_identifier",
            "field_definition_identifier",
            "field_name"
        );

        $this->assertEquals( "index_field_name", $fieldName );
    }

    public function testGetSortFieldNameReturnsNull()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap", "getIndexFieldName" ) );
        $sortClauseMock = $this->getSortClauseMock();

        $mockedFieldMap
            ->expects( $this->once() )
            ->method( "getFieldMap" )
            ->will(
                $this->returnValue(
                    array(
                        "content_type_identifier" => array(
                            "field_definition_identifier" => "field_type_identifier",
                        ),
                    )
                )
            );

        $fieldName = $mockedFieldMap->getSortFieldName(
            $sortClauseMock,
            "non_existent_content_type_identifier",
            "non_existent_field_definition_identifier",
            "field_name"
        );

        $this->assertNull( $fieldName );
    }

    public function testGetIndexFieldNameCustomField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap" ) );

        $customFieldMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\CustomFieldInterface"
        );
        $customFieldMock
            ->expects( $this->once() )
            ->method( "getCustomField" )
            ->with(
                "content_type_identifier",
                "field_definition_identifier"
            )
            ->will(
                $this->returnValue( "custom_field_name" )
            );

        $customFieldName = $mockedFieldMap->getIndexFieldName(
            $customFieldMock,
            "content_type_identifier",
            "field_definition_identifier",
            "dummy",
            "dummy"
        );

        $this->assertEquals( "custom_field_name", $customFieldName );
    }

    public function testGetIndexFieldNameNamedField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap" ) );
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects( $this->once() )
            ->method( "getType" )
            ->with( "field_type_identifier" )
            ->will(
                $this->returnValue( $indexFieldType )
            );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getIndexDefinition" )
            ->will(
                $this->returnValue(
                    array(
                        "field_name" => $searchFieldTypeMock,
                    )
                )
            );

        $indexFieldType->expects( $this->never() )->method( "getDefaultField" );

        $this->fieldNameGeneratorMock
            ->expects( $this->once() )
            ->method( "getName" )
            ->with(
                "field_name",
                "field_definition_identifier",
                "content_type_identifier"
            )
            ->will(
                $this->returnValue( "generated_field_name" )
            );

        $this->fieldNameGeneratorMock
            ->expects( $this->once() )
            ->method( "getTypedName" )
            ->with(
                "generated_field_name",
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Search\\FieldType" )
            )
            ->will(
                $this->returnValue( "generated_typed_field_name" )
            );

        $fieldName = $mockedFieldMap->getIndexFieldName(
            new ArrayObject(),
            "content_type_identifier",
            "field_definition_identifier",
            "field_type_identifier",
            "field_name"
        );

        $this->assertEquals( "generated_typed_field_name", $fieldName );
    }

    public function testGetIndexFieldNameDefaultField()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap" ) );
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects( $this->once() )
            ->method( "getType" )
            ->with( "field_type_identifier" )
            ->will(
                $this->returnValue( $indexFieldType )
            );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getDefaultField" )
            ->will(
                $this->returnValue( "field_name" )
            );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getIndexDefinition" )
            ->will(
                $this->returnValue(
                    array(
                        "field_name" => $searchFieldTypeMock,
                    )
                )
            );

        $this->fieldNameGeneratorMock
            ->expects( $this->once() )
            ->method( "getName" )
            ->with(
                "field_name",
                "field_definition_identifier",
                "content_type_identifier"
            )
            ->will(
                $this->returnValue( "generated_field_name" )
            );

        $this->fieldNameGeneratorMock
            ->expects( $this->once() )
            ->method( "getTypedName" )
            ->with(
                "generated_field_name",
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Search\\FieldType" )
            )
            ->will(
                $this->returnValue( "generated_typed_field_name" )
            );

        $fieldName = $mockedFieldMap->getIndexFieldName(
            new ArrayObject(),
            "content_type_identifier",
            "field_definition_identifier",
            "field_type_identifier",
            null
        );

        $this->assertEquals( "generated_typed_field_name", $fieldName );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameDefaultFieldThrowsRuntimeException()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap" ) );
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects( $this->once() )
            ->method( "getType" )
            ->with( "field_type_identifier" )
            ->will(
                $this->returnValue( $indexFieldType )
            );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getDefaultField" )
            ->will(
                $this->returnValue( "non_existent_field_name" )
            );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getIndexDefinition" )
            ->will(
                $this->returnValue(
                    array(
                        "field_name" => $searchFieldTypeMock,
                    )
                )
            );

        $mockedFieldMap->getIndexFieldName(
            new ArrayObject(),
            "content_type_identifier",
            "field_definition_identifier",
            "field_type_identifier",
            null
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetIndexFieldNameNamedFieldThrowsRuntimeException()
    {
        $mockedFieldMap = $this->getMockedFieldMap( array( "getFieldMap" ) );
        $indexFieldType = $this->getIndexFieldTypeMock();
        $searchFieldTypeMock = $this->getSearchFieldTypeMock();

        $this->fieldRegistryMock
            ->expects( $this->once() )
            ->method( "getType" )
            ->with( "field_type_identifier" )
            ->will(
                $this->returnValue( $indexFieldType )
            );

        $indexFieldType->expects( $this->never() )->method( "getDefaultField" );

        $indexFieldType
            ->expects( $this->once() )
            ->method( "getIndexDefinition" )
            ->will(
                $this->returnValue(
                    array(
                        "field_name" => $searchFieldTypeMock,
                    )
                )
            );

        $mockedFieldMap->getIndexFieldName(
            new ArrayObject(),
            "content_type_identifier",
            "field_definition_identifier",
            "field_type_identifier",
            "non_existent_field_name"
        );
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedFieldMap( array $methods = array() )
    {
        $fieldMap = $this
            ->getMockBuilder(
                "eZ\\Publish\\Core\\Persistence\\Elasticsearch\\Content\\Search\\FieldMap"
            )
            ->setConstructorArgs(
                array(
                    $this->getFieldRegistryMock(),
                    $this->getContentTypeHandlerMock(),
                    $this->getFieldNameGeneratorMock(),
                )
            )
            ->setMethods( $methods )
            ->getMock();

        return $fieldMap;
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldRegistryMock;

    /**
     * @return \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldRegistryMock()
    {
        if ( !isset( $this->fieldRegistryMock ) )
        {
            $this->fieldRegistryMock = $this->getMock(
                "eZ\\Publish\\Core\\Persistence\\Solr\\Content\\Search\\FieldRegistry"
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
            "eZ\\Publish\\SPI\\FieldType\\Indexable"
        );
    }

    /**
     * @return \eZ\Publish\SPI\Search\FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSearchFieldTypeMock()
    {
        return $this->getMock(
            "eZ\\Publish\\SPI\\Search\\FieldType"
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
        if ( !isset( $this->contentTypeHandlerMock ) )
        {
            $this->contentTypeHandlerMock = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler"
            );
        }

        return $this->contentTypeHandlerMock;
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldNameGeneratorMock;

    /**
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldNameGeneratorMock()
    {
        if ( !isset( $this->fieldNameGeneratorMock ) )
        {
            $this->fieldNameGeneratorMock = $this->getMock(
                "eZ\\Publish\\Core\\Persistence\\Elasticsearch\\Content\\Search\\FieldNameGenerator"
            );
        }

        return $this->fieldNameGeneratorMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCriterionMock()
    {
        return $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSortClauseMock()
    {
        return $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\SortClause" )
            ->disableOriginalConstructor()
            ->getMock();
    }
}
