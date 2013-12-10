<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Search\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
class LocationSearchHandlerSortTest extends LanguageAwareTestCase
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
        $ids = $this->getIds( $locations );
        $this->assertEquals( $expectedIds, $ids );
    }

    protected function getIds( $locations )
    {
        $ids = array_map(
            function ( $location )
            {
                return $location->id;
            },
            $locations
        );

        return $ids;
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

    public function testNoSorting()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\ParentLocationId( array( 178 ) ),
                    'offset' => 0,
                    'limit' => 5,
                    'sortClauses' => array()
                )
            )
        );

        $ids = $this->getIds( $locations );
        sort( $ids );
        $this->assertEquals(
            array( 179, 180, 181, 182, 183 ),
            $ids
        );
    }

    public function testSortLocationPathString()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\ParentLocationId( array( 178 ) ),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array( new SortClause\LocationPathString( Query::SORT_DESC ) )
                )
            )
        );

        $this->assertSearchResults(
            array( 186, 185, 184, 183, 182, 181, 180, 179 ),
            $locations
        );
    }

    public function testSortLocationDepth()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 148, 167, 169, 172 ) ),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array( new SortClause\LocationDepth( Query::SORT_ASC ) )
                )
            )
        );

        $this->assertSearchResults(
            array( 167, 172, 169, 148 ),
            $locations
        );
    }

    public function testSortLocationDepthAndPathString()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 141, 142, 143, 144, 146, 147 ) ),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\LocationDepth( Query::SORT_ASC ),
                        new SortClause\LocationPathString( Query::SORT_DESC ),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 147, 146, 141, 144, 143, 142 ),
            $locations
        );
    }

    public function testSortLocationPriority()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 149, 156, 167 ) ),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\LocationPriority( Query::SORT_DESC ),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 167, 156, 149 ),
            $locations
        );
    }

    public function testSortDateModified()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 148, 167, 169, 172 ) ),
                    'offset' => 0,
                    'limit'  => 10,
                    'sortClauses' => array(
                        new SortClause\DateModified(),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 169, 172, 167, 148 ),
            $locations
        );
    }

    public function testSortDatePublished()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 148, 167, 169, 172 ) ),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DatePublished( Query::SORT_DESC ),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 148, 172, 169, 167 ),
            $locations
        );
    }

    public function testSortSectionIdentifier()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId(
                        array( 5, 43, 45, 48, 51, 54, 156, 157 )
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\SectionIdentifier(),
                    )
                )
            )
        );

        // First, results of section 2 should appear, then the ones of 3, 4 and 6
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            2 => array( 5, 45 ),
            3 => array( 43, 51 ),
            4 => array( 48, 54 ),
            6 => array( 156, 157 ),
        );
        $locationIds = $this->getIds( $locations );
        $index = 0;

        foreach ( $idMapSet as $idSet )
        {
            $locationIdsSubset = array_slice( $locationIds, $index, $count = count( $idSet ) );
            $index += $count;
            sort( $locationIdsSubset );
            $this->assertEquals(
                $idSet,
                $locationIdsSubset
            );
        }
    }

    public function testSortContentName()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 13, 15, 44, 45, 228 ) ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\ContentName(),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 228, 15, 13, 45, 44 ),
            $locations
        );
    }

    public function testSortContentId()
    {
        $handler = $this->getLocationSearchHandler();

        $locations = $handler->findLocations(
            new Query(
                array(
                    'filter' => new Criterion\LocationId( array( 13, 15, 44, 45, 228 ) ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    )
                )
            )
        );

        $this->assertSearchResults(
            array( 45, 13, 15, 44, 228 ),
            $locations
        );
    }
}
