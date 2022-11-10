<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Core\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Tests\Limitation\PermissionResolver\BaseLimitationIntegrationTest;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;

class RoleLimitationTest extends BaseLimitationIntegrationTest
{
    private const USERS_GROUP_ID = 4;

    public function userPermissionLimitationProvider(): array
    {
        $allowEditorLimitation = new RoleLimitation();
        $roleService = $this->getRepository()->getRoleService();
        $allowEditorLimitation->limitationValues[] = $roleService->loadRoleByIdentifier('Editor')->id;

        $allowAdministratorLimitation = new RoleLimitation();
        $allowAdministratorLimitation->limitationValues[] = $roleService->loadRoleByIdentifier('Administrator')->id;

        return [
            [[$allowEditorLimitation], false],
            [[$allowAdministratorLimitation], true],
        ];
    }

    /**
     * @dataProvider userPermissionLimitationProvider
     */
    public function testCanUserAssignRole(array $limitations, bool $expectedResult): void
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $adminRoleThatWillBeSet = $roleService->loadRoleByIdentifier('Administrator');
        $this->loginAsEditorUserWithLimitations('role', 'assign', $limitations);

        $this->assertCanUser(
            $expectedResult,
            'role',
            'assign',
            $limitations,
            $userService->loadUser($this->permissionResolver->getCurrentUserReference()->getUserId()),
            [$adminRoleThatWillBeSet]
        );

        $this->assertCanUser(
            $expectedResult,
            'role',
            'assign',
            $limitations,
            $repository->sudo(
                static function (Repository $repository) {
                    return $repository->getUserService()->loadUserGroup(self::USERS_GROUP_ID);
                },
                $repository
            ),
            [$adminRoleThatWillBeSet]
        );
    }
}
