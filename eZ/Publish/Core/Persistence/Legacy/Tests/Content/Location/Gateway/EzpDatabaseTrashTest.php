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
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\API\Repository\Values\Content\Query;

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

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::trashLocation
     * @todo test updated content status
     */
    public function testTrashLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0 ),
                array( 2, 0 ),
                array( 69, 0 ),
                array( 70, 0 ),
            ),
            $query
                ->select( 'node_id', 'priority' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70, 71 ) ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::trashLocation
     */
    public function testTrashLocationUpdateTrashTable()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 71, '/1/2/69/70/71/' ),
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
            array( 'depth', 4 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'main_node_id', 228 ),
            array( 'node_id', 228 ),
            array( 'parent_node_id', 70 ),
            array( 'path_identification_string', '' ),
            array( 'path_string', '/1/2/69/70/228/' ),
            array( 'priority', 0 ),
            array( 'remote_id', '087adb763245e0cdcac593fb4a5996cf' ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::untrashLocation
     * @dataProvider getUntrashedLocationValues
     */
    public function testUntrashLocationDefault( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $handler->untrashLocation( 71 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $property )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 69 ) ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::untrashLocation
     */
    public function testUntrashLocationNewParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $handler->untrashLocation( 71, 1 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( '228', '1', '/1/228/' ) ),
            $query
                ->select( 'node_id', 'parent_node_id', 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 69 ) ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashInvalidLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $handler->untrashLocation( 23 );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $handler->untrashLocation( 71, 1337 );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidOldParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );
        $handler->trashLocation( 70 );

        $handler->untrashLocation( 70 );
        $handler->untrashLocation( 71 );
    }

    public static function getLoadTrashValues()
    {
        return array(
            array( 'node_id', 71 ),
            array( 'priority', 0 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'remote_id', '087adb763245e0cdcac593fb4a5996cf' ),
            array( 'contentobject_id', 69 ),
            array( 'parent_node_id', 70 ),
            array( 'path_identification_string', 'products/software/os_type_i' ),
            array( 'path_string', '/1/2/69/70/71/' ),
            array( 'modified_subnode', 1311065013 ),
            array( 'main_node_id', 71 ),
            array( 'depth', 4 ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::loadTrashByLocation
     * @dataProvider getLoadTrashValues
     */
    public function testLoadTrashByLocationId( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 71 );

        $data = $handler->loadTrashByLocation( 71 );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     */
    public function testListEmptyTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $this->assertEquals(
            array(),
            $handler->listTrashed( 0, null, array() )
        );
    }

    protected function trashSubtree()
    {
        $handler = $this->getLocationGateway();
        $handler->trashLocation( 69 );
        $handler->trashLocation( 70 );
        $handler->trashLocation( 71 );
        $handler->trashLocation( 72 );
        $handler->trashLocation( 73 );
        $handler->trashLocation( 74 );
        $handler->trashLocation( 75 );
        $handler->trashLocation( 76 );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     */
    public function testListFullTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $this->assertEquals(
            8,
            count( $handler->listTrashed( 0, null, array() ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     */
    public function testListTrashLimited()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     * @dataProvider getTrashValues
     */
    public function testListTrashItem( $key, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $trashList = $handler->listTrashed( 0, 1, array() );
        $this->assertEquals( $value, $trashList[0][$key] );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     */
    public function testListTrashSortedPathStringDesc()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

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

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::listTrashed
     */
    public function testListTrashSortedDepth()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

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

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::cleanupTrash
     */
    public function testCleanupTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();
        $handler->cleanupTrash();

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(),
            $query
                ->select( '*' )
                ->from( 'ezcontentobject_trash' )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::removeElementFromTrash
     */
    public function testRemoveElementFromTrash()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $this->trashSubtree();
        $handler->removeElementFromTrash( 71 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(),
            $query
                ->select( '*' )
                ->from( 'ezcontentobject_trash' )
                ->where( $query->expr->eq( 'node_id', 71 ) )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase::countLocationsByContentId
     */
    public function testCountLocationsByContentId()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        self::assertSame( 0, $handler->countLocationsByContentId( 123456789 ) );
        self::assertSame( 1, $handler->countLocationsByContentId( 67 ) );

        // Insert a new node and count again
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( 'ezcontentobject_tree' )
            ->set( 'contentobject_id', $query->bindValue( 67, null, \PDO::PARAM_INT ) )
            ->set( 'contentobject_version', $query->bindValue( 1, null, \PDO::PARAM_INT ) )
            ->set( 'path_string', $query->bindValue( '/1/2/96' ) )
            ->set( 'parent_node_id', $query->bindValue( 96, null, \PDO::PARAM_INT ) )
            ->set( 'remote_id', $query->bindValue( 'some_remote_id' ) );
        $query->prepare()->execute();
        self::assertSame( 2, $handler->countLocationsByContentId( 67 ) );
    }
}

