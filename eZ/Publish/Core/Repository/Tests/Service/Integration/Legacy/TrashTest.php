<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\TrashTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\Integration\TrashBase as BaseTrashServiceTest;

/**
 * Test case for Trash Service using Legacy storage class
 */
class TrashTest extends BaseTrashServiceTest
{
    protected function getRepository()
    {
        try
        {
            return Utils::getRepository();
        }
        catch ( \Exception $e )
        {
            $this->markTestIncomplete(  $e->getMessage() );
        }
    }
}
