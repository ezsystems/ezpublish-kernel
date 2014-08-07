<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\Legacy as Legacy;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;
use eZ\Publish\Core\MVC\Legacy\Kernel;
use Symfony\Component\Filesystem\Filesystem;

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
            $fs = new Filesystem();
            $fs->remove( 'var/test' );
        }
        /** @var $legacyKernel Kernel */
        $legacyKernel = $_ENV['legacyKernel'];
        $legacyKernel->runCallback(
            function ()
            {
                \eZClusterFileHandler::instance()->fileDelete( 'var/test', true );
            },
            false,
            false
        );

        chdir( $this->originalDir );
        parent::tearDown();
    }

    /**
     * Updating mtime isn't supported by the Legacy handler
     */
    public function testUpdateMtime()
    {
        self::markTestSkipped( "Not supported by Legacy io handler" );
    }

    /**
     * Updating ctime isn't supported by the Legacy handler
     */
    public function testUpdateCtime()
    {
        self::markTestSkipped( "Not supported by Legacy io handler" );
    }
}
