<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Legacy\IOTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\IOBase as BaseIOServiceTest,

    eZ\Publish\Core\Repository\Tests\Service\Legacy\IOUploadTest;

/**
 * Test case for IO Service using Legacy storage class
 *
 */
class IOTest extends BaseIOServiceTest
{
    public function __construct()
    {
        $this->fileUploadTest = new IOUploadTest();
    }

    protected function getRepository( array $serviceSettings )
    {
        if ( !class_exists( 'eZClusterFileHandler' ) )
            $this->markTestSkipped( 'Cluster files could not be loaded' );

        try
        {
            return include 'common.php';
        }
        catch ( \Exception $e )
        {
            $this->markTestIncomplete(  $e->getMessage() );
        }
    }
}
