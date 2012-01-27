<?php
/**
 * File contains: ezp\Base\Tests\AutoloadTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\ClassLoader;

/**
 * Test case for Autoloader class
 *
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test multiple code repositories support
     *
     * @covers \ezp\Base\ClassLoader::load
     */
    public function testReturnValuesOnFailure()
    {
        $loader = new ClassLoader( array( 'ezp' => 'ezp',
                                          'xyz' => 'xyz/Pasta' ) );

        self::assertFalse( $loader->load( "ezp\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "\\ezp\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "xyz\\Pizza\\Box" ) );
        self::assertFalse( $loader->load( "\\xyz\\Pizza\\Box" ) );
        self::assertNull( $loader->load( "NotHere\\Pizza\\Box" ) );// void
        self::assertNull( $loader->load( "\\NotHere\\Pizza\\Box" ) );// void
    }
}
