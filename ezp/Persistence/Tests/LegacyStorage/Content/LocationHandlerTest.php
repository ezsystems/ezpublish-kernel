<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Location\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content,
    ezp\Persistence;

/**
 * Test case for LocationHandlerTest
 */
class LocationHandlerTest extends TestCase
{
    /**
     * CMocked content handler instance
     *
     * @var \ezp\Persistence\LegacyStorage\Content\ContentHandler
     */
    protected $contentHandler;

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    protected function getLocationHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new Content\LocationHandler(
            $this->contentHandler = $this->getMock( '\ezp\Persistence\LegacyStorage\Content\ContentHandler' ),
            new Content\LocationGateway\EzcDatabase( $dbHandler )
        );
    }

    protected function getContentObject()
    {
        $contentObject = new \ezp\Persistence\Content();
        $contentObject->id = 68;

        return $contentObject;
    }

    public function testMoveSubtreePathUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $handler->move( 69, 77 );

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

    public function testMoveSubtreeModificationTimeUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $handler->move( 69, 77 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/77/69/' ),
                array( '/1/2/77/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
        );
    }

    public function testMoveSubtreeAssignementUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $handler->move( 69, 77 );

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

    public function testHideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $handler->hide( 69 );

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

    public function testHideSubtreeModificationTimeUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $handler->hide( 69 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/69/' ),
                array( '/1/2/69/70/' ),
                array( '/1/2/69/70/71/' ),
                array( '/1/2/69/72/' ),
                array( '/1/2/69/72/73/' ),
                array( '/1/2/69/72/74/' ),
                array( '/1/2/69/72/75/' ),
                array( '/1/2/69/76/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $handler->hide( 69 );
        $handler->unhide( 69 );

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

    public function testHideUnhideSubtreeModificationTimeUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $handler->hide( 69 );
        $handler->unhide( 69 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/69/' ),
                array( '/1/2/69/70/' ),
                array( '/1/2/69/70/71/' ),
                array( '/1/2/69/72/' ),
                array( '/1/2/69/72/73/' ),
                array( '/1/2/69/72/74/' ),
                array( '/1/2/69/72/75/' ),
                array( '/1/2/69/76/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideParentTree()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $handler->hide( 69 );
        $handler->hide( 70 );
        $handler->unhide( 69 );

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
        $handler = $this->getLocationHandler();
        $handler->hide( 69 );
        $handler->hide( 70 );
        $handler->unhide( 70 );

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
        $this->markTestIncomplete( '@TODO: Reploduce in eZ Publish -- currently results in transaction errors.' );
    }

    public function testUpdatePriority()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
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

    public function testUpdatePrioritySubtreeModificationTimeUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $handler->updatePriority( 70, 23 );

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

    public function testCreateLocation()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();

        $this->contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 68 )
            ->will( $this->returnValue( $this->getContentObject() ) );

        $handler->createLocation( 68, 77 );

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

    public static function getLocationValues()
    {
        return array(
            array( 'contentobject_id', 68 ),
            array( 'contentobject_is_published', 1 ),
            array( 'contentobject_version', 1 ),
            array( 'depth', 3 ),
            array( 'is_hidden', 0 ),
            array( 'is_invisible', 0 ),
            array( 'main_node_id', 70 ),
            array( 'parent_node_id', 77 ),
            array( 'path_identification_string', 'solutions/software' ),
            array( 'priority', 0 ),
            array( 'remote_id', '34d37f301508408dfe6b68f1e1d238ad' ),
            array( 'sort_field', 1 ),
            array( 'sort_order', 1 ),
        );
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getLocationValues
     */
    public function testCreateLocationValues( $field, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();

        $this->contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 68 )
            ->will( $this->returnValue( $this->getContentObject() ) );

        $handler->createLocation( 68, 77 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->eq( 'node_id', 228 ) )
        );
    }

    public function testCreateLocationSubtreeModificationTimeUpdate()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $this->contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 68 )
            ->will( $this->returnValue( $this->getContentObject() ) );

        $handler->createLocation( 68, 77 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array(
                array( '/1/' ),
                array( '/1/2/' ),
                array( '/1/2/77/' ),
                array( '/1/2/77/228/' ),
            ),
            $query
                ->select( 'path_string' )
                ->from( 'ezcontentobject_tree' )
                ->where( $query->expr->gte( 'modified_subnode', $time ) )
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
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationHandler();
        $time    = time();
        $this->contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 68 )
            ->will( $this->returnValue( $this->getContentObject() ) );

        $handler->createLocation( 68, 77 );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            array( array( $value ) ),
            $query
                ->select( $field )
                ->from( 'eznode_assignment' )
                ->where( $query->expr->lAnd(
                    $query->expr->eq( 'contentobject_id', 68 ),
                    $query->expr->eq( 'parent_node', 77 )
                ) )
        );
    }
}
