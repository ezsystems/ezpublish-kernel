<?php
/**
 * File containing the SysInfoTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests;
use ezp\Io\SysInfo,
    ezp\Base\Configuration;

class SysInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group io
     * @covers \ezp\Io\SysInfo::varDirectory
     */
    public function testVarDirectory()
    {
        $varDir = 'some/dummy/dir';
        Configuration::getInstance( 'site' )->set( 'FileSettings', 'VarDir', $varDir );
        self::assertSame( $varDir, SysInfo::varDirectory() );
    }

    /**
     * @group io
     * @covers \ezp\Io\SysInfo::varDirectory
     */
    public function testStorageDirectory()
    {
        $varDir = 'some/dummy/dir';
        $storageDir = 'path/to/storage';
        Configuration::getInstance( 'site' )->set( 'FileSettings', 'VarDir', $varDir );
        Configuration::getInstance( 'site' )->set( 'FileSettings', 'StorageDir', $storageDir );
        self::assertSame( "$varDir/$storageDir", SysInfo::storageDirectory() );
    }
}
