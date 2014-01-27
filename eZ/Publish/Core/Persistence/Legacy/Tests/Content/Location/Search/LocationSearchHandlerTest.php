<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Search\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Search;

use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTime;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Integer;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Url;

/**
 * Test case for LocationSearchHandler
 */
class LocationSearchHandlerTest extends LanguageAwareTestCase
{
    protected static $setUp = false;

    /**
     * Only set up once for these read only tests on a large fixture
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     *
     * @return void
     */
    public function setUp()
    {
        if ( !self::$setUp )
        {
            parent::setUp();
            $this->insertDatabaseFixture( __DIR__ . '/../../SearchHandler/_fixtures/full_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    /**
     * Assert that the elements are
     */
    protected function assertSearchResults( $expectedIds, $locations )
    {
        $result = array_map(
            function ( $location )
            {
                return $location->id;
            },
            $locations
        );

        sort( $result );

        $this->assertEquals( $expectedIds, $result );
    }

    /**
     * Returns the location search handler to test
     *
     * This method returns a fully functional search handler to perform tests on.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Search\Handler
     */
    protected function getLocationSearchHandler()
    {
        $rules = array();
        foreach ( glob( __DIR__ . '/../../../Tests/TransformationProcessor/_fixtures/transformations/*.tr' ) as $file )
        {
            $rules[] = str_replace( self::getInstallationDir(), '', $file );
        }

        $transformationProcessor = new Persistence\TransformationProcessor\DefinitionBased(
            new Persistence\TransformationProcessor\DefinitionBased\Parser( self::getInstallationDir() ),
            new Persistence\TransformationProcessor\PcreCompiler(
                new Persistence\Utf8Converter()
            ),
            $rules
        );
        return new Location\Search\Handler(
            new Location\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getLanguageMaskGenerator(),
                $transformationProcessor,
                new ConverterRegistry(
                    array(
                        'ezdatetime' => new DateAndTime(),
                        'ezinteger' => new Integer(),
                        'ezstring' => new TextLine(),
                        'ezprice' => new Integer(),
                        'ezurl' => new Url()
                    )
                )
            ),
            $this->getLocationMapperMock()
        );
    }

    /**
     * Returns a location mapper mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        $mapperMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper',
            array( 'createLocationsFromRows' )
        );
        $mapperMock
            ->expects( $this->any() )
            ->method( 'createLocationsFromRows' )
            ->with( $this->isType( 'array' ) )
            ->will(
                $this->returnCallback(
                    function ( $rows )
                    {
                        $locations = array();
                        foreach ( $rows as $row )
                        {
                            $locationId = (int)$row['node_id'];
                            if ( !isset( $locations[$locationId] ) )
                            {
                                $locations[$locationId] = new SPILocation();
                                $locations[$locationId]->id = $locationId;
                            }
                        }
                        return array_values( $locations );
                    }
                )
            );
        return $mapperMock;
    }

    public function testFindWithoutOffsetLimit()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Location\Id( 2 )
                )
            )
        );

        $this->assertEquals( 1, count( $locations ) );
    }

    public function testFindWithZeroLimit()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Location\Id( 2 ),
                    'offset' => 0,
                    'limit' => 0,
                )
            )
        );

        $this->assertEquals( array(), $locations );
    }

    /**
     * Issue with PHP_MAX_INT limit overflow in databases
     */
    public function testFindWithNullLimit()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Location\Id( 2 ),
                    'offset' => 0,
                    'limit' => null,
                )
            )
        );

        $this->assertEquals( 1, count( $locations ) );
    }

    /**
     * Issue with offsetting to the nonexistent results produces \ezcQueryInvalidParameterException exception.
     */
    public function testFindWithOffsetToNonexistent()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Location\Id( 2 ),
                    'offset'    => 1000,
                    'limit'     => null,
                )
            )
        );

        $this->assertEquals( 0, count( $locations ) );
    }

    public function testLocationIdFilter()
    {
        $this->assertSearchResults(
            array( 12, 13 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Id(
                            array( 4, 12, 13 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testParentLocationIdFilter()
    {
        $this->assertSearchResults(
            array( 12, 13, 14, 44, 227 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\ParentLocationId( 5 ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationIdAndCombinatorFilter()
    {
        $this->assertSearchResults(
            array( 13 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Location\Id(
                                    array( 4, 12, 13 )
                                ),
                                new Criterion\Location\Id(
                                    array( 13, 44 )
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationIdParentLocationIdAndCombinatorFilter()
    {
        $this->assertSearchResults(
            array( 44, 160 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Location\Id(
                                    array( 2, 44, 160, 166 )
                                ),
                                new Criterion\Location\ParentLocationId(
                                    array( 5, 156 )
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentDepthFilterEq()
    {
        $this->assertSearchResults(
            array( 2, 5, 43, 48, 58 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::EQ, 1 ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentDepthFilterIn()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 43, 44, 48, 51, 52, 53, 54, 56, 58, 59, 69, 77, 86, 96, 107, 153, 156, 167, 190, 227 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::IN, array( 1, 2 ) ),
                    )
                )
            )
        );
    }

    public function testContentDepthFilterBetween()
    {
        $this->assertSearchResults(
            array( 2, 5, 43, 48, 58 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::BETWEEN, array( 0, 1 ) ),
                    )
                )
            )
        );
    }

    public function testContentDepthFilterGreaterThan()
    {
        $this->assertSearchResults(
            array( 99, 102, 135, 136, 137, 139, 140, 142, 143, 144, 145, 148, 151, 174, 175, 177, 194, 196, 197, 198, 199, 200, 201, 202, 203, 205, 206, 207, 208, 209, 210, 211, 212, 214, 215 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::GT, 4 ),
                    )
                )
            )
        );
    }

    public function testContentDepthFilterGreaterThanOrEqual()
    {
        $this->assertSearchResults(
            array( 99, 102, 135, 136, 137, 139, 140, 142, 143, 144, 145, 148, 151, 174, 175, 177, 194, 196, 197, 198, 199, 200, 201, 202, 203, 205, 206, 207, 208, 209, 210, 211, 212, 214, 215 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::GTE, 5 ),
                    )
                )
            )
        );
    }

    public function testContentDepthFilterLessThan()
    {
        $this->assertSearchResults(
            array( 2, 5, 43, 48, 58 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::LT, 2 ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentDepthFilterLessThanOrEqual()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 43, 44, 48, 51, 52, 53, 54, 56, 58, 59, 69, 77, 86, 96, 107, 153, 156, 167, 190, 227 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Depth( Criterion\Operator::LTE, 2 ),
                    )
                )
            )
        );
    }

    public function testLocationPriorityFilter()
    {
        $this->assertSearchResults(
            array( 156, 167, 190 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Priority(
                            Criterion\Operator::BETWEEN,
                            array( 1, 10 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationRemoteIdFilter()
    {
        $this->assertSearchResults(
            array( 2, 5 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\RemoteId(
                            array( '3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983' )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testVisibilityFilterVisible()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Visibility(
                            Criterion\Location\Visibility::VISIBLE
                        ),
                        'limit' => 5,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testVisibilityFilterHidden()
    {
        $this->assertSearchResults(
            array( 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Location\Visibility(
                            Criterion\Location\Visibility::HIDDEN
                        ),
                    )
                )
            )
        );
    }

    public function testLocationNotCombinatorFilter()
    {
        $this->assertSearchResults(
            array( 2, 5 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Location\Id(
                                    array( 2, 5, 12, 356 )
                                ),
                                new Criterion\LogicalNot(
                                    new Criterion\Location\Id(
                                        array( 12, 13, 14 )
                                    )
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationOrCombinatorFilter()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LogicalOr(
                            array(
                                new Criterion\Location\Id(
                                    array( 2, 5, 12 )
                                ),
                                new Criterion\Location\Id(
                                    array( 12, 13, 14 )
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentIdFilterEquals()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ContentId( 223 ),
                    )
                )
            )
        );
    }


    public function testContentIdFilterIn()
    {
        $this->assertSearchResults(
            array( 225, 226, 227 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ContentId(
                            array( 223, 224, 225 )
                        ),
                    )
                )
            )
        );
    }

    public function testContentTypeGroupFilter()
    {
        $this->assertSearchResults(
            array( 5, 12, 13, 14, 15, 44, 45, 227, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ContentTypeGroupId( 2 ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentTypeIdFilter()
    {
        $this->assertSearchResults(
            array( 15, 45, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ContentTypeId( 4 ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentTypeIdentifierFilter()
    {
        $this->assertSearchResults(
            array( 43, 48, 51, 52, 53 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ContentTypeIdentifier( 'folder' ),
                        'limit' => 5,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testObjectStateIdFilter()
    {
        $this->assertSearchResults(
            array( 5, 12, 13, 14, 15, 43, 44, 45, 48, 51 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ObjectStateId( 1 ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\ContentId ),
                    )
                )
            )
        );
    }

    public function testObjectStateIdFilterIn()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\ObjectStateId( array( 1, 2 ) ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testRemoteIdFilter()
    {
        $this->assertSearchResults(
            array( 5, 45 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RemoteId(
                            array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testSectionFilter()
    {
        $this->assertSearchResults(
            array( 5, 12, 13, 14, 15, 44, 45, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\SectionId( array( 2 ) ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreater()
    {
        $this->assertSearchResults(
            array( 12, 227, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GT,
                            1311154214
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreaterOrEqual()
    {
        $this->assertSearchResults(
            array( 12, 15, 227, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GTE,
                            1311154214
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedIn()
    {
        $this->assertSearchResults(
            array( 12, 15, 227, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::IN,
                            array( 1311154214, 1311154215 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedBetween()
    {
        $this->assertSearchResults(
            array( 12, 15, 227, 228 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::BETWEEN,
                            array( 1311154213, 1311154215 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterCreatedBetween()
    {
        $this->assertSearchResults(
            array( 68, 133, 227 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::CREATED,
                            Criterion\Operator::BETWEEN,
                            array( 1299780749, 1311154215 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerWrongUserId()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            2
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerAdministrator()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            14
                        ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerEqAMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerInAMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::IN,
                            array( 226 )
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorEqAMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorInAMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::IN,
                            array( 226 )
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            11
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMember()
    {
        $this->assertSearchResults(
            array( 225 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            array( 11 )
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            13
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            array( 13 )
                        ),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilter()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LanguageCode( 'eng-US' ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilterIn()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LanguageCode( array( 'eng-US', 'eng-GB' ) ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\Location\Id ),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilterWithAlwaysAvailable()
    {
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48, 51, 52, 53, 58, 59, 70, 72, 76, 78, 82 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LanguageCode( 'eng-GB', true ),
                        'limit' => 20,
                        'sortClauses' => array( new SortClause\ContentId ),
                    )
                )
            )
        );
    }

    public function testMatchAllFilter()
    {
        $this->markTestIncomplete( "Needs SearchHit" );
        $result = $this->getLocationSearchHandler()->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\MatchAll(),
                    'limit' => 10,
                    'sortClauses' => array( new SortClause\Location\Id ),
                )
            )
        );

        $this->assertCount( 100, $result );
        $this->assertSearchResults(
            array( 2, 5, 12, 13, 14, 15, 43, 44, 45, 48 ),
            $result
        );
    }

    public function testFullTextFilter()
    {
        $this->assertSearchResults(
            array( 193 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\FullText( 'applied webpage' ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextWildcardFilter()
    {
        $this->assertSearchResults(
            array( 193 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\FullText( 'applie*' ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextDisabledWildcardFilter()
    {
        $this->markTestIncomplete( "Needs DI" );
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler(
                array( 'enableWildcards' => false )
            )->findLocations(
                    new LocationQuery(
                        array(
                            'filter' => new Criterion\FullText( 'applie*' ),
                            'limit' => 10,
                        )
                    )
                )
            );
    }

    public function testFullTextFilterStopwordRemoval()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\FullText( 'the' ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextFilterNoStopwordRemoval()
    {
        $this->markTestIncomplete( "Needs SearchHit" );
        $handler = $this->getLocationSearchHandler(
            array(
                'searchThresholdValue' => PHP_INT_MAX
            )
        );

        $result = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\FullText(
                        'the'
                    ),
                    'limit' => 10,
                )
            )
        );

        $this->assertEquals(
            10,
            count(
                array_map(
                    function ( $hit )
                    {
                        return $hit->valueObject->contentInfo->id;
                    },
                    $result->searchHits
                )
            )
        );
    }

    public function testRelationListFilterContainsSingle()
    {
        $this->assertSearchResults(
            array( 69 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array( 60 )
                        ),
                    )
                )
            )
        );
    }

    public function testRelationListFilterContainsSingleNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array( 4 )
                        ),
                    )
                )
            )
        );
    }

    public function testRelationListFilterContainsArray()
    {
        $this->assertSearchResults(
            array( 69 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array( 60, 75 )
                        ),
                    )
                )
            )
        );
    }

    public function testRelationListFilterContainsArrayNotMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array( 60, 64 )
                        ),
                    )
                )
            )
        );
    }

    public function testRelationListFilterInArray()
    {
        $this->assertSearchResults(
            array( 69, 77 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::IN,
                            array( 60, 64 )
                        ),
                    )
                )
            )
        );
    }

    public function testRelationListFilterInArrayNotMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\RelationList(
                            'billboard',
                            Criterion\Operator::IN,
                            array( 4, 10 )
                        ),
                    )
                )
            )
        );
    }

    public function testFieldFilter()
    {
        $this->assertSearchResults(
            array( 12 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'members'
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterIn()
    {
        $this->assertSearchResults(
            array( 12, 44 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::IN,
                            array( 'members', 'anonymous users' )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsPartial()
    {
        $this->assertSearchResults(
            array( 44 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::CONTAINS,
                            'nonymous use'
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsSimple()
    {
        $this->assertSearchResults(
            array( 79 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643880
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsSimpleNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterBetween()
    {
        $this->assertSearchResults(
            array( 71, 73, 74 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\Field(
                            'price',
                            Criterion\Operator::BETWEEN,
                            array( 10000, 1000000 )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterOr()
    {
        $this->assertSearchResults(
            array( 12, 71, 73, 74 ),
            $this->getLocationSearchHandler()->findLocations(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\LogicalOr(
                            array(
                                new Criterion\Field(
                                    'name',
                                    Criterion\Operator::EQ,
                                    'members'
                                ),
                                new Criterion\Field(
                                    'price',
                                    Criterion\Operator::BETWEEN,
                                    array( 10000, 1000000 )
                                )
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }
}
