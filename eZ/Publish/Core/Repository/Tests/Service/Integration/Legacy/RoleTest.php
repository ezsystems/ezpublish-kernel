<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\Integration\RoleBase as BaseRoleServiceTest;
use Exception;

/**
 * Test case for Role Service using Legacy storage class.
 */
class RoleTest extends BaseRoleServiceTest
{
    protected function getRepository()
    {
        try {
            return Utils::getRepository();
        } catch (Exception $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }
}
