<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Location\Gateway\EzpDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Location\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Storage\Legacy\Content\Location\Gateway\EzcDatabase,
    ezp\Persistence;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Location\Gateway\EzcDatabase
 */
class EzpDatabaseTest extends TestCase
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

    public static function getLoadLocationValues()
    {
        return array(
            array( 'node_id', 77 ),
            array( 'priority', 0 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'remote_id', 'dbc2f3c8716c12f32c379dbf0b1cb133' ),
            array( 'contentobject_id', 75 ),
            array( 'parent_node_id', 2 ),
            array( 'path_identification_string', 'solutions' ),
            array( 'path_string', '/1/2/77/' ),
            array( 'modified_subnode', 1311065017 ),
            array( 'main_node_id', 77 ),
            array( 'depth', 2 ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @dataProvider getLoadLocationValues
     */
    public function testLoadLocation( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeData( 77 );

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadInvalidLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeData( 1337 );
    }

    public function testMoveSubtreePathUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->moveSubtreeNodes( '/1/2/69/', '/1/2/77/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 65, '/1/2/' ),
                array( 67, '/1/2/77/69/' ),
                array( 69, '/1/2/77/69/70/71/' ),
                array( 73, '/1/2/77/69/72/75/' ),
                array( 75, '/1/2/77/' ),
            ),
            $query
                ->select( 'contentobject_id', 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 69, 71, 75, 77, 2 ) ) )
        );
    }

    public function testMoveSubtreeAssignementUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->updateNodeAssignement( 67, 77 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 67, 1, 0, 53, 1, 5, 77, '9cec85d730eec7578190ee95ce5a36f5', 0, 2, 1 ),
            ),
            $query
                ->select( '*' )
                ->from( 'eznode_assignment' )
                ->where( $query->expr->eq( 'contentobject_id', 67 ) )
        );
    }

    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $time = time();
        $handler->updateSubtreeModificationTime( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/69/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
        );
    }

    public function testHideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 1, 1 ),
                array( 75, 0, 1 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 75 ) ) )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->unhideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 0, 0 ),
                array( 75, 0, 0 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 75 ) ) )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideParentTree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->hideSubtree( '/1/2/69/70/' );
        $handler->unhideSubtree( '/1/2/69/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 0, 0 ),
                array( 70, 1, 1 ),
                array( 71, 0, 1 ),
                array( 75, 0, 0 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70, 71, 75 ) ) )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhidePartialSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->hideSubtree( '/1/2/69/' );
        $handler->hideSubtree( '/1/2/69/70/' );
        $handler->unhideSubtree( '/1/2/69/70/' );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0, 0 ),
                array( 2, 0, 0 ),
                array( 69, 1, 1 ),
                array( 70, 0, 1 ),
                array( 71, 0, 1 ),
                array( 75, 0, 1 ),
            ),
            $query
                ->select( 'node_id', 'is_hidden', 'is_invisible' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70, 71, 75 ) ) )
        );
    }

    public function testSwapLocations()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->swap( 70, 78 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 70, 76 ),
                array( 78, 68 ),
            ),
            $query
                ->select( 'node_id', 'contentobject_id' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 70, 78 ) ) )
        );
    }

    public function testUpdatePriority()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->updatePriority( 70, 23 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 1, 0 ),
                array( 2, 0 ),
                array( 69, 0 ),
                array( 70, 23 ),
            ),
            $query
                ->select( 'node_id', 'priority' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'node_id', array( 1, 2, 69, 70 ) ) )
        );
    }

    public function testCreateLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct( array(
                'contentId' => 68,
                'remoteId'  => 'some_id',
            ) ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( 70, '/1/2/69/70/' ),
                array( 228, '/1/2/77/228/' ),
                array( 77, '/1/2/77/' ),
            ),
            $query
                ->select( 'node_id', 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->in( 'contentobject_id', array( 68, 75 ) ) )
        );
    }

    public static function getCreateLocationValues()
    {
        return array(
            array( 'contentobject_id', 68 ),
            array( 'contentobject_is_published', 0 ),
            array( 'contentobject_version', 1 ),
            array( 'depth', 3 ),
            array( 'is_hidden', false ),
            array( 'is_invisible', false ),
            array( 'main_node_id', 42 ),
            array( 'parent_node_id', 77 ),
            array( 'path_identification_string', '' ),
            array( 'priority', 1 ),
            array( 'remote_id', 'some_id' ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getCreateLocationValues
     */
    public function testCreateLocationValues( $field, $value )
    {
        if ( $value === null )
        {
            $this->markTestIncomplete( 'Proper value setting yet unknown.' );
        }

        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct( array(
                'contentId'      => 68,
                'contentVersion' => 1,
                'remoteId'       => 'some_id',
                'mainLocationId' => 42,
                'priority'       => 1,
                'remoteId'       => 'some_id',
                'sortField'      => 1,
                'sortOrder'      => 1,
            ) ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->eq( 'node_id', 228 ) )
        );
    }

    public static function getNodeAssignmentValues()
    {
        return array(
            array( 'contentobject_version', 1 ),
            array( 'from_node_id', 0 ),
            array( 'id', 214 ),
            array( 'is_main', 0 ),
            array( 'op_code', 2 ),
            array( 'parent_node', 77 ),
            array( 'parent_remote_id', '' ),
            array( 'remote_id', 0 ),
            array( 'sort_field', 2 ),
            array( 'sort_order', 0 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getNodeAssignmentValues
     */
    public function testCreateLocationNodeAssignmentCreation( $field, $value )
    {
        if ( $value === null )
        {
            $this->markTestIncomplete( 'Proper value setting yet unknown.' );
        }

        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct( array(
                'contentId'      => 68,
                'contentVersion' => 1,
                'remoteId'       => 'some_id',
                'mainLocationId' => 1,
                'priority'       => 1,
                'remoteId'       => 'some_id',
                'sortField'      => 1,
                'sortOrder'      => 1,
            ) ),
            array(
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'eznode_assignment' )
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq( 'contentobject_id', 68 ),
                        $query->expr->eq( 'parent_node', 77 )
                    )
                )
        );
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
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUntrashInvalidLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $handler->untrashLocation( 23 );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUntrashLocationInvalidParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69, 1337 );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUntrashLocationInvalidOldParent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();
        $handler->trashSubtree( '/1/2/69/' );

        $handler->untrashLocation( 69 );
        $handler->untrashLocation( 70 );
    }

    public function testSetSectionForSubtree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../_fixtures/contentobjects.php' );
        $handler = $this->getLocationGateway();
        $handler->setSectionForSubtree( '/1/2/69/70/', 23 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( 68 ), array( 69 ) ),
            $query
                ->select( 'id' )
                ->from( 'ezcontentobject' )
                ->where( $query->expr->eq( 'section_id', 23 ) )
        );
    }
}

