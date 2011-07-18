<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\User\UserHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\User;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\User,
    ezp\Persistence;

/**
 * Test case for UserHandlerTest
 */
class UserHandlerTest extends TestCase
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

    protected function getUserHandler()
    {
        return new User\UserHandler(
            new User\UserGateway\EzcDatabase(
                $this->getDatabaseHandler()
            )
        );
    }

    public function testCreateUser()
    {
        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id      = 42;
        $user->login   = 'kore';
        $user->pwd     = '1234567890';
        $user->hashAlg = 'md5';
    
        $handler->createUser( $user );
        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->handler->createSelectQuery()
                ->select( 'COUNT( * )' )
                ->from( 'ezuser' ),
            'Expected one user to be created.'
        );
    }

    /**
     * @expectedException \PDOException
     */
    public function testCreateDuplicateUser()
    {
        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id      = 42;
        $user->login   = 'kore';
        $user->pwd     = '1234567890';
        $user->hashAlg = 'md5';
    
        $handler->createUser( $user );
        $handler->createUser( $user );
    }

    /**
     * @expectedException \PDOException
     */
    public function testInsertIncompleteUser()
    {
        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id      = 42;
    
        $handler->createUser( $user );
    }
}
