<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Legacy as Legacy;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;
use eZ\Publish\Core\MVC\Legacy\Kernel;
use ezcBaseFile;

/**
 * Handler test
 */
class LegacyTest extends BaseHandlerTest
{
    protected $legacyPath;
    protected $originalDir;

    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    protected function getIOHandler()
    {
        if ( !isset( $_ENV['legacyKernel'] ) )
        {
            self::markTestSkipped(
                'Legacy kernel is needed to run these tests. Please ensure that "legacyKernel" environment variable is properly set with a eZ\\Publish\\Core\\MVC\\Legacy\\Kernel instance'
            );
        }

        return new Legacy( 'var/test/storage', $_ENV['legacyKernel'] );
    }

    public function setUp()
    {
        parent::setUp();
        $this->legacyPath = $_ENV['legacyPath'];
        $this->originalDir = getcwd();
    }

    protected function tearDown()
    {
        chdir( $this->legacyPath );
        if ( file_exists( 'var/test' ) )
        {
            ezcBaseFile::removeRecursive( 'var/test' );
        }
        if ( file_exists( 'var/othertest' ) )
        {
            ezcBaseFile::removeRecursive( 'var/othertest' );
        }
        /** @var $legacyKernel Kernel */
        $legacyKernel = $_ENV['legacyKernel'];
        $legacyKernel->runCallback(
            function ()
            {
                \eZClusterFileHandler::instance()->fileDelete( 'var/test', true );
            }
        );
        $legacyKernel->runCallback(
            function ()
            {
                \eZClusterFileHandler::instance()->fileDelete( 'var/othertest', true );
            }
        );

        chdir( $this->originalDir );
        parent::tearDown();
    }

    /**
     * Updating mtime isn't supported by the Legacy handler
     */
    public function testUpdateMtime()
    {
        self::markTestIncomplete( "Not supported by Legacy io handler, aka incomplete" );
    }

    /**
     * Updating ctime isn't supported by the Legacy handler
     */
    public function testUpdateCtime()
    {
        self::markTestIncomplete( "Not supported by Legacy io handler, aka incomplete" );
    }

    /**
     * Tests that loading a file located outside the vardir passes
     */
    public function testFileOutsideVarDir()
    {
        // problem: the logic of IOService isn't gonna work well here...
        // ImageStorage (only affected) would have var/otherfolder/storage/path,
        // but to load the file, it would call IOService::getExternalPath( $absolutePath )
        // Is it possible to return anything even a tiny bit consistent / that would work at all here ?


        $this->IOHandler->load( 'var/othertest/path/outside/vardir' );
    }
}
