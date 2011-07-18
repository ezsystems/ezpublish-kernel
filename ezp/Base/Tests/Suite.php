<?php
/**
 * File contains: ezp\Base\Tests\Suite class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use PHPUnit_Framework_TestSuite;

/**
 * Test suite for base module
 *
 */
class Suite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite( "Base" );

        $suite->addTestSuite( __NAMESPACE__ . "\\AutoloadTest" );
        $suite->addTestSuite( __NAMESPACE__ . "\\IniParserTest" );
        $suite->addTestSuite( __NAMESPACE__ . "\\ReadOnlyCollectionTest" );
        $suite->addTestSuite( __NAMESPACE__ . "\\TypeCollectionTest" );

        return $suite;
    }
}
