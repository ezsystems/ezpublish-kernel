<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Section\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Section\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase,

    ezp\Persistence\Content\Section;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected $databaseGateway;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::__construct
     */
    public function testCtor()
    {
        $handler = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected function getDatabaseGateway()
    {
        if ( !isset( $this->databaseGateway ) )
        {
            $this->databaseGateway = new EzcDatabase(
                 $this->getDatabaseHandler()
            );
        }
        return $this->databaseGateway;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
