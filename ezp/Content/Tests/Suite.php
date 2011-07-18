<?php
/**
 * File contains: ezp\Content\Tests\Suite class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestSuite;

/**
 * Test suite for content module
 *
 */
class Suite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite( "Content" );
        
        $suite->addTestSuite( __NAMESPACE__ . "\\LocationTest" );
        $suite->addTestSuite( __NAMESPACE__ . "\\ContentTest" );
        // $suite->addTestSuite( __NAMESPACE__ . "\\CriteriaCollectionTest"  );
        $suite->addTestSuite( __NAMESPACE__ . "\\TranslationTest" );
        $suite->addTestSuite( __NAMESPACE__ . "\\QueryBuilderTest" );

        return $suite;
    }
}
