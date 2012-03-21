<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Legacy\TrashTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\TrashBase as BaseTrashServiceTest;

/**
 * Test case for Trash Service using Legacy storage class
 *
 */
class TrashTest extends BaseTrashServiceTest
{
    protected function getRepository( array $serviceSettings )
    {
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
