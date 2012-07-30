<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase
     */
    protected $databaseGateway;

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
