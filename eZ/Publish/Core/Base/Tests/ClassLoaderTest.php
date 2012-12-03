<?php
/**
 * File contains: Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests;
use eZ\Publish\Core\Base\ClassLoader;
use PHPUnit_Framework_TestCase;

/**
 * Test class
 */
class ClassLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Base\ClassLoader::load
     */
    public function testFileCheckReturnValuesOnFailure()
    {
        $loader = new ClassLoader(
            array(
                'eZ' => '',
                'xyz\\Pizza' => 'vendor/'
            )
        );

        self::assertFalse( $loader->load( "eZ\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "\\eZ\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "xyz\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "\\xyz\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "NotHere\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "\\NotHere\\Pizza\\Box" ) );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ClassLoader::load
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFailure()
    {
        $loader = new ClassLoader(
            array(
                'eZ' => '',
                'xyz' => 'vendor/'
            )
        );

        include $loader->load( "eZ\\Will\\Fail", true );
    }

    /**
     * @covers \eZ\Publish\Core\Base\ClassLoader::load
     */
    public function testMapping()
    {
        $loader = new ClassLoader(
            array(
                'eZ' => '/',
                'xyz\\Pizza' => '/vendor/zzz/',
                'xyz' => '/vendor/'
            )
        );

        self::assertEquals( '/eZ/Pizza/Box.php', $loader->load( "eZ\\Pizza\\Box", true ) );
        self::assertEquals( '/eZ/Pizza/Box.php', $loader->load( "\\eZ\\Pizza\\Box", true ) );
        self::assertEquals( '/eZ/Pizza/Paper/Box.php', $loader->load( "eZ\\Pizza\\Paper_Box", true ) );
        self::assertEquals( '/eZ/Pizza_Paper/White/Box.php', $loader->load( "eZ\\Pizza_Paper\\White_Box", true ) );

        self::assertEquals( '/vendor/zzz/xyz/Pizza/Box.php', $loader->load( "xyz\\Pizza\\Box", true ) );
        self::assertEquals( '/vendor/zzz/xyz/Pizza/Box.php', $loader->load( "\\xyz\\Pizza\\Box", true ) );
        self::assertEquals( '/vendor/zzz/xyz/Pizza/Paper/Box.php', $loader->load( "xyz\\Pizza\\Paper_Box", true ) );

        self::assertEquals( '/vendor/xyz/Bolognese/Box.php', $loader->load( "xyz\\Bolognese\\Box", true ) );
        self::assertEquals( '/vendor/xyz/Bolognese/Box.php', $loader->load( "\\xyz\\Bolognese\\Box", true ) );
        self::assertEquals( '/vendor/xyz/Bolognese/Paper/Box.php', $loader->load( "xyz\\Bolognese\\Paper_Box", true ) );
    }
}
