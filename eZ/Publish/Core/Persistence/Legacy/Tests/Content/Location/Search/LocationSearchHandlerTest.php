<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Search\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Search;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

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
        return new Location\Search\Handler(
            new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() ),
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
            new Query(
                array(
                    'filter' => new Criterion\LocationId( 2 )
                )
            )
        );

        $this->assertEquals( 1, count( $locations ) );
    }

    public function testFindWithZeroLimit()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( 2 ),
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
            new Query(
                array(
                    'filter' => new Criterion\LocationId( 2 ),
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
            new Query(
                array(
                    'filter' => new Criterion\LocationId( 2 ),
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
                new Query(
                    array(
                        'filter' => new Criterion\LocationId(
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
                new Query(
                    array(
                        'filter' => new Criterion\ParentLocationId( 5 ),
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
                new Query(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationId(
                                    array( 4, 12, 13 )
                                ),
                                new Criterion\LocationId(
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
                new Query(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationId(
                                    array( 2, 44, 160, 166 )
                                ),
                                new Criterion\ParentLocationId(
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
}
