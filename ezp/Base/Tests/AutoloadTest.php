<?php
/**
 * File contains: ezp\Base\Tests\AutoloadTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\Autoloader;

/**
 * Test case for Autoloader class
 *
 */
class AutoloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that multiple instances of Autoloader manages to live side by side independent of
     * ezcBase Autoloader being loaded or not, and registered or not.
     * @covers \ezp\Base\Autoloader::load
     */
    public function testEzcLoading()
    {
        // Make sure ezc is already loaded
        class_exists( 'ezcBase' );
        // Then try to re load it and make sure no errors happen as well as ezcBase still / ends up
        // being a registered autoloader
        $loader = new Autoloader( array(), array() );
        $loader->load( 'ezcBase' );
        $this->assertContains( array( 'ezcBase', 'autoload' ), spl_autoload_functions(), 'ezcBase not registered as autoloader'  );
    }
}
