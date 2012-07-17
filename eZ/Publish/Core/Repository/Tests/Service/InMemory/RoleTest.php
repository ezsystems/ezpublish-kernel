<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\RoleTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;
use eZ\Publish\Core\Repository\Tests\Service\RoleBase as BaseRoleServiceTest;

/**
 * Test case for Role Service using InMemory storage class
 */
class RoleTest extends BaseRoleServiceTest
{
    protected function getRepository( array $serviceSettings )
    {
        return Utils::getRepository( $serviceSettings );
    }
}
