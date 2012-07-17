<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Legacy\IOTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\IOBase as BaseIOServiceTest,

    eZ\Publish\Core\Repository\Tests\Service\Legacy\IOUploadPHPT;

/**
 * Test case for IO Service using Legacy storage class
 */
class IOTest extends BaseIOServiceTest
{
    protected function tearDown()
    {
        $legacyKernel = $_ENV['legacyKernel'];
        $legacyKernel->enterLegacyRootDir();
        if ( file_exists( 'var/test' ) )
        {
            \ezcBaseFile::removeRecursive( 'var/test' );
        }
        $legacyKernel->leaveLegacyRootDir();
        parent::tearDown();
    }

    /**
     * @return \PHPUnit_Extensions_PhptTestCase
     */
    protected function getFileUploadTest()
    {
        return new IOUploadPHPT();
    }

    protected function getRepository( array $serviceSettings )
    {
        if ( !class_exists( 'eZClusterFileHandler' ) )
            $this->markTestSkipped( 'Cluster files could not be loaded' );

        try
        {
            if ( !isset( $_ENV['legacyKernel'] ) )
            {
                self::markTestSkipped(
                    'Legacy kernel is needed to run these tests. Please ensure that "legacyKernel" environment variable is properly set with a eZ\\Publish\\Legacy\\Kernel instance'
                );
            }

            $repository = include 'common.php';
            $repository->setLegacyKernel( $_ENV['legacyKernel'] );
            return $repository;
        }
        catch ( \Exception $e )
        {
            $this->markTestIncomplete(  $e->getMessage() );
        }
    }
}
