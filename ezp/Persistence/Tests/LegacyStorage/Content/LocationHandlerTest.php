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
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    /**
     * Inserts database fixture from $fileName.
     *
     * @todo: Duplication of code in
     * Content/Type/ContentTypeGateway/EzcDatabaseTest.php -- will be moved
     * into base class soon.
     *
     * @param string $fileName
     * @return void
     */
    protected function insertFixture( $fileName )
    {
        $data = require( __DIR__ . '/_fixtures/' . $fileName );
        $db   = $this->getDatabaseHandler();

        foreach ( $data as $table => $rows )
        {
            foreach ( $rows as $row )
            {
                $q = $db->createInsertQuery();
                $q->insertInto( $db->quoteIdentifier( $table ) );
                foreach ( $row as $col => $val )
                {
                    $q->set(
                        $db->quoteIdentifier( $col ),
                        $q->bindValue( $val )
                    );
                }
                $stmt = $q->prepare();
                $stmt->execute();
            }
        }
    }

    protected function getLocationHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new Content\LocationHandler(
            new Content\LocationGateway\EzcDatabase( $dbHandler )
        );
    }

    public function testMoveSubtreePathUpdate()
    {
        $this->insertFixture( 'full_example_tree.php' );
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
        $this->insertFixture( 'full_example_tree.php' );
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
        $this->insertFixture( 'full_example_tree.php' );
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
}
