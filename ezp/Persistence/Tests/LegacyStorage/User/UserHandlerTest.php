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
    ezp\Persistence\LegacyStorage\User;

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

        $this->assertTrue( true );
    }
}
