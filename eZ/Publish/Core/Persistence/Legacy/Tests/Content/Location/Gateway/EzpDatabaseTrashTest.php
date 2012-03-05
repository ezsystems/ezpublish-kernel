<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway\EzpDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\Trashed,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    ezp\Content\Query,
    eZ\Publish\SPI\Persistence;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase
 */
class EzpDatabaseTrashTest extends TestCase
{
    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    protected function getLocationGateway()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new EzcDatabase( $dbHandler );
    }

    public function testTrashSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0 ),
                array( 2, 0 ),
            ),
            $query
                ->select( 'node_id', 'priority' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70 ) ) )
        );
    }

    public function testTrashSubtreeUpdateTrashTable()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 69, '/1/2/69/' ),
                array( 70, '/1/2/69/70/' ),
                array( 71, '/1/2/69/70/71/' ),
                array( 72, '/1/2/69/72/' ),
                array( 73, '/1/2/69/72/73/' ),
                array( 74, '/1/2/69/72/74/' ),
                array( 75, '/1/2/69/72/75/' ),
                array( 76, '/1/2/69/76/' ),
            ),
            $query
                ->select( 'node_id', 'path_string' )
                ->from( 'ezcontentobject_trash' )
        );
    }

    public static function getUntrashedLocationValues()
    {
        return array(
            array( 'contentobject_is_published', 1 ),
            array( 'contentobject_version', 1 ),
            array( 'depth', 2 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'main_node_id', 228 ),
            array( 'node_id', 228 ),
            array( 'parent_node_id', 2 ),
            array( 'path_identification_string', '' ),
            array( 'path_string', '/1/2/228/' ),
            array( 'priority', 0 ),
            array( 'remote_id', '9cec85d730eec7578190ee95ce5a36f5' ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @dataProvider getUntrashedLocationValues
     */
    public function testUntrashLocationDefault( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $property )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 67 ) ) )
        );
    }

    public function testUntrashLocationNewParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69, 1 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( '228', '1', '/1/228/' ) ),
            $query
                ->select( 'node_id', 'parent_node_id', 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 67 ) ) )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashInvalidLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $handler->untrashLocation( 23 );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69, 1337 );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidOldParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69 );
        $handler->untrashLocation( 70 );
    }

    public static function getLoadTrashValues()
    {
        return array(
            array( 'node_id', 69 ),
            array( 'priority', 0 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'remote_id', '9cec85d730eec7578190ee95ce5a36f5' ),
            array( 'contentobject_id', 67 ),
            array( 'parent_node_id', 2 ),
            array( 'path_identification_string', 'products' ),
            array( 'path_string', '/1/2/69/' ),
            array( 'modified_subnode', 1311065014 ),
            array( 'main_node_id', 69 ),
            array( 'depth', 2 ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @dataProvider getLoadTrashValues
     */
    public function testLoadTrashByLocationId( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $data = $handler->loadTrashByLocation( 69 );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    public function testListEmptyTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $this->assertEquals(
            array(),
            $handler->listTrashed( 0, null, array() )
        );
    }

    public function testListFullTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $this->assertEquals(
            8,
            count( $handler->listTrashed( 0, null, array() ) )
        );
    }

    public function testListTrashLimited()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $this->assertEquals(
            5,
            count( $handler->listTrashed( 0, 5, array() ) )
        );
    }

    public static function getTrashValues()
    {
        return array(
            array( 'contentobject_id', 67 ),
            array( 'contentobject_version', 1 ),
            array( 'depth', 2 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'main_node_id', 69 ),
            array( 'modified_subnode', 1311065014 ),
            array( 'node_id', 69 ),
            array( 'parent_node_id', 2 ),
            array( 'path_identification_string', 'products' ),
            array( 'path_string', '/1/2/69/' ),
            array( 'priority', 0 ),
            array( 'remote_id', '9cec85d730eec7578190ee95ce5a36f5' ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @dataProvider getTrashValues
     */
    public function testListTrashItem( $key, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $trashList = $handler->listTrashed( 0, 1, array() );
        $this->assertEquals( $value, $trashList[0][$key] );
    }

    public function testListTrashSortedPathStringDesc()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $this->assertEquals(
            array(
                '/1/2/69/76/',
                '/1/2/69/72/75/',
                '/1/2/69/72/74/',
                '/1/2/69/72/73/',
                '/1/2/69/72/',
                '/1/2/69/70/71/',
                '/1/2/69/70/',
                '/1/2/69/',
            ),
            array_map(
                function ( $trashItem )
                {
                    return $trashItem['path_string'];
                },
                $trashList = $handler->listTrashed( 0, null, array(
                    new SortClause\LocationPathString( Query::SORT_DESC ),
                ) )
            )
        );
    }

    public function testListTrashSortedDepth()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $this->assertEquals(
            array(
                '/1/2/69/',
                '/1/2/69/76/',
                '/1/2/69/72/',
                '/1/2/69/70/',
                '/1/2/69/72/75/',
                '/1/2/69/72/74/',
                '/1/2/69/72/73/',
                '/1/2/69/70/71/',
            ),
            array_map(
                function ( $trashItem )
                {
                    return $trashItem['path_string'];
                },
                $trashList = $handler->listTrashed( 0, null, array(
                    new SortClause\LocationDepth(),
                    new SortClause\LocationPathString( Query::SORT_DESC ),
                ) )
            )
        );
    }
}

