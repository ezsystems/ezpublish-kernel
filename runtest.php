#!/usr/bin/env php
<?php
/**
 * File containing a simplified runtests script for PHPUnit
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package tests
 */

set_time_limit( 0 );

require_once 'autoload.php';
require_once 'PHPUnit/Autoload.php';


/**
 * Simple test suite that maps all other tests suits by convention
 * Suite needs to be in [ezp|ezx]/<module>/tests/Suite.php
 *
 * @internal
 */
class ezpNextTestSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'ezp next Test Suite' );
        foreach ( glob( '{ezp,ezx}/*', GLOB_BRACE | GLOB_ONLYDIR ) as $path )
        {
            if ( file_exists( "$path/tests/Suite.php" ) )
                $this->addTestSuite( str_replace( DIRECTORY_SEPARATOR, '\\', "$path\\tests\\Suite" )  );

        }
    }
}

PHPUnit_TextUI_TestRunner::run( new ezpNextTestSuite(), array( 'backupGlobals' => false,
                                                               'backupStaticAttributes' => false ) );

