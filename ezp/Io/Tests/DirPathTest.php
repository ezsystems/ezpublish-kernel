<?php
/**
 * File containing the DirPathTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests;
use ezp\Io\DirPath;

class DirPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group io
     * @covers \ezp\Io\DirPath::clean
     */
    public function testCleanIncludeEndSeparator()
    {
        $path = 'where/is/brian';
        self::assertSame( "$path/", DirPath::clean( $path, true ) );
        self::assertSame( "$path/", DirPath::clean( "$path/", true ) );
    }

    /**
     * @group io
     * @covers \ezp\Io\DirPath::clean
     */
    public static function testCleanIncludeNoEndSeparator()
    {
        $path = 'where/is/brian';
        self::assertSame( $path, DirPath::clean( $path, false ) );
        self::assertSame( $path, DirPath::clean( "$path/", false ) );
    }

    /**
     * @group io
     * @covers \ezp\Io\DirPath::clean
     */
    public static function testCleanSeveralPaths()
    {
        $aPath = array(
            'where/is/brian',
            'brian/is/in/the/kitchen'
        );
        self::assertSame( implode( '/', $aPath ), DirPath::clean( $aPath ) );
    }

    /**
     * @group io
     * @covers \ezp\Io\DirPath::clean
     */
    public static function testCleanMultipleSeparators()
    {
        $path = 'slash//is///great/at/playing////guitar';
        self::assertSame( 'slash/is/great/at/playing/guitar', DirPath::clean( $path ) );
    }
}
