<?php
/**
 * File containing the QueryBuilderTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests\BinaryStorage;
use ezp\Base\BinaryRepository,
    ezp\Io\BinaryFile, ezp\Io\BinaryFileCreateStruct, ezp\Io\BinaryFileUpdateStruct,
    eZClusterFileHandler;

class BinaryRepositoryLegacyTest extends \ezp\Io\Tests\BinaryRepositoryTest
{
    public function setUp()
    {
        if ( !class_exists( 'eZClusterFileHandler' ) )
        {
            if ( !file_exists( 'ezpublish/kernel/classes/ezclusterfilehandler.php' ) )
            {
                self::markTestSkipped( "Cluster files not linked: ln -s /path/to/ezpublish ." );
            }

            // include mock dependencies
            $dependenciesPath = __DIR__ . DIRECTORY_SEPARATOR . basename( __FILE__, '.php' ) . DIRECTORY_SEPARATOR;
            include $dependenciesPath  . 'ezexecution.php';
            include $dependenciesPath  . 'ezpextensionoptions.php';
            include $dependenciesPath  . 'ezextension.php';
            include $dependenciesPath  . 'ezdebugsetting.php';
            include $dependenciesPath  . 'ezdebug.php';
            include $dependenciesPath  . 'ezini.php';

            include( 'ezpublish/lib/ezfile/classes/ezfile.php' );
            include( 'ezpublish/lib/ezfile/classes/ezdir.php' );
            include( 'ezpublish/lib/ezfile/classes/ezfilehandler.php' );
            include( 'ezpublish/kernel/classes/ezclusterfilehandler.php' );
            include( 'ezpublish/kernel/classes/clusterfilehandlers/ezfsfilehandler.php' );
        }

        $this->binaryRepository = new BinaryRepository( 'legacy' );
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
        self::markTestSkipped();
    }

    /**
     * Updating ctime isn't supported by the Legacy handler
     */
    public function testUpdateCtime()
    {
        self::markTestSkipped();
    }
}
?>
