<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RelationProcessorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct;

/**
 * Mock Test case for RelationProcessor service
 */
class RelationProcessorTest extends BaseServiceMockTest
{
    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::__construct
     */
    public function testConstructor()
    {
        $this->markTestSkipped( "This test in some cases with PHP 5.3.3 causes segmentation fault" );
        $relationProcessor = $this->getPartlyMockedRelationProcessor();

        $this->assertAttributeSame(
            $this->getRepositoryMock(),
            "repository",
            $relationProcessor
        );

        $this->assertAttributeSame(
            $this->getPersistenceMock(),
            "persistenceHandler",
            $relationProcessor
        );
    }

    /**
     * Test for the getFieldRelations() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::getFieldRelations
     */
    public function testGetFieldRelations()
    {
        $relationProcessor = $this->getPartlyMockedRelationProcessor( array( "appendFieldRelations" ) );
        $repositoryMock = $this->getRepositoryMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $contentServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\ContentService" );
        $contentInfoMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $contentTypeMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" );
        $fieldValueMock = $this->getMockForAbstractClass( "eZ\\Publish\\Core\\FieldType\\Value" );
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $contentMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Content",
            array( "__get", "getFields", "getFieldValue", "getVersionInfo", "getFieldsByLanguage" )
        );

        $contentMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "contentType" )
            ->will( $this->returnValue( $contentTypeMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentService" )
            ->will( $this->returnValue( $contentServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $contentServiceMock->expects( $this->once() )
            ->method( "loadContentByContentInfo" )
            ->with( $contentInfoMock )
            ->will( $this->returnValue( $contentMock ) );

        $contentMock->expects( $this->once() )
            ->method( "getFields" )
            ->will(
                $this->returnValue(
                    array(
                        new Field(
                            array(
                                "fieldDefIdentifier" => "test_fielddefinition_identifier",
                                "value" => 42,
                            )
                        )
                    )
                )
            );

        $contentTypeMock->expects( $this->once() )
            ->method( "getFieldDefinition" )
            ->with( "test_fielddefinition_identifier" )
            ->will(
                $this->returnValue(
                    new FieldDefinition(
                        array(
                            "id" => 24,
                            "fieldTypeIdentifier" => "test_fieldtype_identifier",
                        )
                    )
                )
            );

        $fieldTypeServiceMock->expects( $this->once() )
            ->method( "buildFieldType" )
            ->with( "test_fieldtype_identifier" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( 42 )
            ->will( $this->returnValue( $fieldValueMock ) );

        $relationProcessor->expects( $this->once() )
            ->method( "appendFieldRelations" )
            ->with(
                array(),
                array(),
                $fieldTypeMock,
                $fieldValueMock,
                24
            );

        $relationProcessor->getFieldRelations( $contentInfoMock );
    }

    public function providerForTestAppendRelations()
    {
        return array(
            array(
                array( Relation::FIELD => array( 100 ) ),
                array( Relation::FIELD => array( 42 => array( 100 => 0 ) ) ),
            ),
            array(
                array( Relation::LINK => array( "contentIds" => array( 100 ) ) ),
                array( Relation::LINK => array( 100 => 0 ) ),
            ),
            array(
                array( Relation::EMBED => array( "contentIds" => array( 100 ) ) ),
                array( Relation::EMBED => array( 100 => 0 ) ),
            ),
            array(
                array(
                    Relation::FIELD => array( 100 ),
                    Relation::LINK => array( "contentIds" => array( 100 ) ),
                    Relation::EMBED => array( "contentIds" => array( 100 ) ),
                ),
                array(
                    Relation::FIELD => array( 42 => array( 100 => 0 ) ),
                    Relation::LINK => array( 100 => 0 ),
                    Relation::EMBED => array( 100 => 0 ),
                ),
            ),
            array(
                array( Relation::LINK => array( "locationIds" => array( 100 ) ) ),
                array( Relation::LINK => array( 200 => true ) ),
            ),
            array(
                array(
                    Relation::LINK => array(
                        "locationIds" => array( 100 ),
                        "contentIds" => array( 100 ),
                    )
                ),
                array( Relation::LINK => array( 100 => 0, 200 => true ) ),
            ),
            array(
                array( Relation::EMBED => array( "locationIds" => array( 100 ) ) ),
                array( Relation::EMBED => array( 200 => true ) ),
            ),
            array(
                array(
                    Relation::EMBED => array(
                        "locationIds" => array( 100 ),
                        "contentIds" => array( 100 ),
                    )
                ),
                array( Relation::EMBED => array( 100 => 0, 200 => true ) ),
            ),
            array(
                array(
                    Relation::LINK => array(
                        "locationIds" => array( 100 ),
                        "contentIds" => array( 100 ),
                    ),
                    Relation::EMBED => array(
                        "locationIds" => array( 101 ),
                        "contentIds" => array( 100 ),
                    ),
                ),
                array(
                    Relation::LINK => array( 100 => 0, 200 => true ),
                    Relation::EMBED => array( 100 => 0, 201 => true ),
                ),
            ),
            array(
                array(
                    Relation::FIELD => array( 100 ),
                    Relation::LINK => array(
                        "locationIds" => array( 100 ),
                        "contentIds" => array( 100 ),
                    ),
                    Relation::EMBED => array(
                        "locationIds" => array( 101 ),
                        "contentIds" => array( 100 ),
                    ),
                ),
                array(
                    Relation::FIELD => array( 42 => array( 100 => 0 ) ),
                    Relation::LINK => array( 100 => 0, 200 => true ),
                    Relation::EMBED => array( 100 => 0, 201 => true ),
                ),
            ),
        );
    }

    /**
     * Test for the appendFieldRelations() method.
     *
     * @dataProvider providerForTestAppendRelations
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::appendFieldRelations
     */
    public function testAppendFieldRelations( array $fieldRelations, array $expected )
    {
        $repositoryMock = $this->getRepositoryMock();
        $relationProcessor = $this->getPartlyMockedRelationProcessor();
        $fieldValueMock = $this->getMockForAbstractClass( "eZ\\Publish\\Core\\FieldType\\Value" );
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $locationServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\LocationService" );
        $locationCallCount = 0;

        $repositoryMock->expects( $this->any() )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( "getRelations" )
            ->with( $this->equalTo( $fieldValueMock ) )
            ->will( $this->returnValue( $fieldRelations ) );

        $this->assertLocationServiceExpectation(
            $locationServiceMock,
            $fieldRelations,
            Relation::LINK,
            $locationCallCount
        );
        $this->assertLocationServiceExpectation(
            $locationServiceMock,
            $fieldRelations,
            Relation::EMBED,
            $locationCallCount
        );

        $relations = array();
        $locationIdToContentIdMapping = array();

        $relationProcessor->appendFieldRelations(
            $relations,
            $locationIdToContentIdMapping,
            $fieldTypeMock,
            $fieldValueMock,
            42
        );

        $this->assertEquals( $expected, $relations );
    }

    /**
     * Assert loading Locations to find Content id in {@link RelationProcessor::appendFieldRelations()} method.
     */
    protected function assertLocationServiceExpectation( $locationServiceMock, $fieldRelations, $type, &$callCounter )
    {
        if ( isset( $fieldRelations[$type]["locationIds"] ) )
        {
            foreach ( $fieldRelations[$type]["locationIds"] as $locationId )
            {
                $locationServiceMock->expects( $this->at( $callCounter ) )
                    ->method( "loadLocation" )
                    ->with( $this->equalTo( $locationId ) )
                    ->will(
                        $this->returnValue(
                            new Location(
                                array( "contentInfo" => new ContentInfo( array( "id" => $locationId + 100 ) ) )
                            )
                        )
                    );

                $callCounter += 1;
            }
        }
    }

    /**
     * Test for the appendFieldRelations() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::appendFieldRelations
     */
    public function testAppendFieldRelationsLocationMappingWorks()
    {
        $repositoryMock = $this->getRepositoryMock();
        $relationProcessor = $this->getPartlyMockedRelationProcessor();
        $fieldValueMock = $this->getMockForAbstractClass( "eZ\\Publish\\Core\\FieldType\\Value" );
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $locationServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\LocationService" );

        $repositoryMock->expects( $this->any() )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $fieldTypeMock->expects( $this->once() )
            ->method( "getRelations" )
            ->with( $this->equalTo( $fieldValueMock ) )
            ->will(
                $this->returnValue(
                    array(
                        Relation::FIELD => array( 100 ),
                        Relation::LINK => array(
                            "locationIds" => array( 100 ),
                            "contentIds" => array( 100 ),
                        ),
                        Relation::EMBED => array(
                            "locationIds" => array( 100 ),
                            "contentIds" => array( 100 ),
                        ),
                    )
                )
            );

        $locationServiceMock->expects( $this->once() )
            ->method( "loadLocation" )
            ->with( $this->equalTo( 100 ) )
            ->will(
                $this->returnValue(
                    new Location(
                        array( "contentInfo" => new ContentInfo( array( "id" => 200 ) ) )
                    )
                )
            );

        $relations = array();
        $locationIdToContentIdMapping = array();

        $relationProcessor->appendFieldRelations(
            $relations,
            $locationIdToContentIdMapping,
            $fieldTypeMock,
            $fieldValueMock,
            42
        );

        $this->assertEquals(
            array(
                Relation::FIELD => array( 42 => array( 100 => 0 ) ),
                Relation::LINK => array( 100 => 0, 200 => true ),
                Relation::EMBED => array( 100 => 0, 200 => true ),
            ),
            $relations
        );
    }

    /**
     * Test for the processFieldRelations() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::processFieldRelations
     */
    public function testProcessFieldRelationsNoChanges()
    {
        $relationProcessor = $this->getPartlyMockedRelationProcessor();
        $contentHandlerMock = $this->getPersistenceMockHandler( 'Content\\Handler' );
        $contentTypeMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" );

        $contentTypeMock->expects( $this->once() )
            ->method( "getFieldDefinition" )
            ->with( $this->equalTo( "identifier42" ) )
            ->will( $this->returnValue( new FieldDefinition( array( "id" => 42 ) ) ) );

        $contentHandlerMock->expects( $this->never() )->method( "addRelation" );
        $contentHandlerMock->expects( $this->never() )->method( "removeRelation" );

        $existingRelations = array(
            $this->getStubbedRelation( 1, Relation::COMMON, null, 10 ),
            $this->getStubbedRelation( 2, Relation::EMBED, null, 11 ),
            $this->getStubbedRelation( 3, Relation::LINK, null, 12 ),
            $this->getStubbedRelation( 4, Relation::FIELD, 42, 13 ),
            // Legacy Storage cases - composite entries
            $this->getStubbedRelation(
                5,
                Relation::EMBED | Relation::COMMON,
                null,
                14
            ),
            $this->getStubbedRelation(
                6,
                Relation::LINK | Relation::COMMON,
                null,
                15
            ),
            $this->getStubbedRelation(
                7,
                Relation::EMBED | Relation::LINK,
                null,
                16
            ),
            $this->getStubbedRelation(
                8,
                Relation::EMBED | Relation::LINK | Relation::COMMON,
                null,
                17
            ),
        );
        $inputRelations = array(
            Relation::EMBED => array_flip( array( 11, 14, 16, 17 ) ),
            Relation::LINK => array_flip( array( 12, 15, 16, 17 ) ),
            Relation::FIELD => array( 42 => array_flip( array( 13 ) ) ),
        );

        $relationProcessor->processFieldRelations(
            $inputRelations,
            24,
            2,
            $contentTypeMock,
            $existingRelations
        );
    }

    /**
     * Test for the processFieldRelations() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::processFieldRelations
     */
    public function testProcessFieldRelationsAddsRelations()
    {
        $relationProcessor = $this->getPartlyMockedRelationProcessor();
        $contentHandlerMock = $this->getPersistenceMockHandler( 'Content\\Handler' );
        $contentTypeMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" );

        $existingRelations = array(
            $this->getStubbedRelation( 1, Relation::COMMON, null, 10 ),
            $this->getStubbedRelation( 2, Relation::EMBED, null, 11 ),
            $this->getStubbedRelation( 3, Relation::LINK, null, 12 ),
            // Legacy Storage cases - composite entries
            $this->getStubbedRelation(
                5,
                Relation::EMBED | Relation::COMMON,
                null,
                14
            ),
            $this->getStubbedRelation(
                6,
                Relation::LINK | Relation::COMMON,
                null,
                15
            ),
            $this->getStubbedRelation(
                7,
                Relation::EMBED | Relation::LINK,
                null,
                16
            ),
        );
        $inputRelations = array(
            Relation::EMBED => array_flip( array( 11, 14, 16, 17 ) ),
            Relation::LINK => array_flip( array( 12, 15, 16, 17 ) ),
            Relation::FIELD => array( 42 => array_flip( array( 13 ) ) ),
        );

        $contentTypeMock->expects( $this->never() )->method( "getFieldDefinition" );
        $contentHandlerMock->expects( $this->never() )->method( "removeRelation" );

        $contentHandlerMock->expects( $this->at( 0 ) )
            ->method( "addRelation" )
            ->with(
                new CreateStruct(
                    array(
                        "sourceContentId" => 24,
                        "sourceContentVersionNo" => 2,
                        "sourceFieldDefinitionId" => null,
                        "destinationContentId" => 17,
                        "type" => Relation::EMBED
                    )
                )
            );

        $contentHandlerMock->expects( $this->at( 1 ) )
            ->method( "addRelation" )
            ->with(
                new CreateStruct(
                    array(
                        "sourceContentId" => 24,
                        "sourceContentVersionNo" => 2,
                        "sourceFieldDefinitionId" => null,
                        "destinationContentId" => 17,
                        "type" => Relation::LINK
                    )
                )
            );

        $contentHandlerMock->expects( $this->at( 2 ) )
            ->method( "addRelation" )
            ->with(
                new CreateStruct(
                    array(
                        "sourceContentId" => 24,
                        "sourceContentVersionNo" => 2,
                        "sourceFieldDefinitionId" => 42,
                        "destinationContentId" => 13,
                        "type" => Relation::FIELD
                    )
                )
            );

        $relationProcessor->processFieldRelations(
            $inputRelations,
            24,
            2,
            $contentTypeMock,
            $existingRelations
        );
    }

    /**
     * Test for the processFieldRelations() method.
     *
     * @covers \eZ\Publish\Core\Repository\RelationProcessor::processFieldRelations
     */
    public function testProcessFieldRelationsRemovesRelations()
    {
        $relationProcessor = $this->getPartlyMockedRelationProcessor();
        $contentHandlerMock = $this->getPersistenceMockHandler( 'Content\\Handler' );
        $contentTypeMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" );

        $existingRelations = array(
            $this->getStubbedRelation( 1, Relation::COMMON, null, 10 ),
            $this->getStubbedRelation( 2, Relation::EMBED, null, 11 ),
            $this->getStubbedRelation( 3, Relation::LINK, null, 12 ),
            $this->getStubbedRelation( 4, Relation::FIELD, 42, 13 ),
            // Legacy Storage cases - composite entries
            $this->getStubbedRelation(
                5,
                Relation::EMBED | Relation::COMMON,
                null,
                14
            ),
            $this->getStubbedRelation(
                6,
                Relation::LINK | Relation::COMMON,
                null,
                15
            ),
            $this->getStubbedRelation(
                7,
                Relation::EMBED | Relation::LINK,
                null,
                16
            ),
            $this->getStubbedRelation(
                8,
                Relation::EMBED | Relation::LINK | Relation::COMMON,
                null,
                17
            ),
        );
        $inputRelations = array(
            Relation::EMBED => array_flip( array( 11, 14, 17 ) ),
            Relation::LINK => array_flip( array( 12, 15, 17 ) ),
        );

        $contentHandlerMock->expects( $this->never() )->method( "addRelation" );

        $contentTypeMock->expects( $this->once() )
            ->method( "getFieldDefinition" )
            ->with( $this->equalTo( "identifier42" ) )
            ->will( $this->returnValue( new FieldDefinition( array( "id" => 42 ) ) ) );

        $contentHandlerMock->expects( $this->at( 0 ) )
            ->method( "removeRelation" )
            ->with(
                $this->equalTo( 7 ),
                $this->equalTo( Relation::EMBED )
            );

        $contentHandlerMock->expects( $this->at( 1 ) )
            ->method( "removeRelation" )
            ->with(
                $this->equalTo( 7 ),
                $this->equalTo( Relation::LINK )
            );

        $contentHandlerMock->expects( $this->at( 2 ) )
            ->method( "removeRelation" )
            ->with(
                $this->equalTo( 4 ),
                $this->equalTo( Relation::FIELD )
            );

        $relationProcessor->processFieldRelations(
            $inputRelations,
            24,
            2,
            $contentTypeMock,
            $existingRelations
        );
    }

    protected function getStubbedRelation( $id, $type, $fieldDefinitionId, $contentId )
    {
        return new \eZ\Publish\Core\Repository\Values\Content\Relation(
            array(
                "id" => $id,
                "type" => $type,
                "destinationContentInfo" => new ContentInfo( array( "id" => $contentId ) ),
                "sourceFieldDefinitionIdentifier" => $fieldDefinitionId ?
                    "identifier" . $fieldDefinitionId :
                    null,
            )
        );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\RelationProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedRelationProcessor( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RelationProcessor",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()
            )
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldTypeServiceMock()
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\FieldTypeService",
            array(),
            array(),
            '',
            false
        );
    }
}
