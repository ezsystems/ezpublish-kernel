<?php
/**
 * File containing the ezp\Io\Tests\BinaryStorage\BinaryRepositoryLegacyTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests\BinaryStorage;
use ezp\Base\ServiceContainer,
    ezp\Io\BinaryStorage\Legacy,
    ezp\Io\BinaryFile,
    ezp\Io\BinaryFileCreateStruct,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\Tests\BinaryRepositoryTest,
    eZClusterFileHandler,
    ezcBaseFile;

/**
 * @fixme This class should be named LegacyTest according to the file name or
 *        the file name must be adapted.
 */
class BinaryRepositoryLegacyTest extends BinaryRepositoryTest
{
    public function setUp()
    {
        // Include mock dependencies
        $dependenciesPath = __DIR__ . DIRECTORY_SEPARATOR . basename( __FILE__, '.php' ) . DIRECTORY_SEPARATOR;
        include_once $dependenciesPath  . 'ezexecution.php';
        include_once $dependenciesPath  . 'ezpextensionoptions.php';
        include_once $dependenciesPath  . 'ezextension.php';
        include_once $dependenciesPath  . 'ezdebugsetting.php';
        include_once $dependenciesPath  . 'ezdebug.php';
        include_once $dependenciesPath  . 'ezini.php';

        // First check if eZClusterFileHandler was loaded by autoloader
        if ( !class_exists( 'eZClusterFileHandler' ) )
        {
            // Secondly include manually using deprecated symlink structure
            if ( !file_exists( 'ezpublish/kernel/classes/ezclusterfilehandler.php' ) )
            {
                self::markTestSkipped( "Cluster files could not be loaded, place api inside eZ Publish, update config.php 'repositories' and run using eg: phpunit -c extension/api/phpunit.xml" );
            }

            include 'ezpublish/lib/ezfile/classes/ezfile.php';
            include 'ezpublish/lib/ezfile/classes/ezdir.php';
            include 'ezpublish/lib/ezfile/classes/ezfilehandler.php';
            include 'ezpublish/kernel/classes/ezclusterfilehandler.php';
            include 'ezpublish/kernel/classes/clusterfilehandlers/ezfsfilehandler.php';
        }

        $sc = new ServiceContainer(
            array(
                '@io_handler' => new Legacy()
            )
        );
        $this->binaryService = $sc->getRepository()->getIoService();
        $this->imageInputPath = realpath( __DIR__ . DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    public function tearDown()
    {
        if ( file_exists( 'var/test' ) )
        {
            ezcBaseFile::removeRecursive( 'var/test' );
        }
    }

    /**
     * Updating mtime isn't supported by the Legacy handler
     */
    public function testUpdateMtime()
    {
        self::markTestSkipped( "Not supported by Legacy io handler, aka incomplete" );
    }

    /**
     * Updating ctime isn't supported by the Legacy handler
     */
    public function testUpdateCtime()
    {
        self::markTestSkipped( "Not supported by Legacy io handler, aka incomplete" );
    }
}
