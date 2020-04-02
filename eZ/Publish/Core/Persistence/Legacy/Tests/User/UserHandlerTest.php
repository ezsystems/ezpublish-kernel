<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\User;

use DateInterval;
use DateTime;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler\ObjectStateHandler as ObjectStateLimitationHandler;
use eZ\Publish\SPI\Persistence;
use eZ\Publish\SPI\Persistence\User\Handler;
use eZ\Publish\SPI\Persistence\User\Role;
use LogicException;

/**
 * Test case for UserHandlerTest.
 */
class UserHandlerTest extends TestCase
{
    private const TEST_USER_ID = 42;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getUserHandler(User\Gateway $userGateway = null): Handler
    {
        $connection = $this->getDatabaseConnection();

        return new User\Handler(
            $userGateway ?? new User\Gateway\DoctrineDatabase($connection),
            new User\Role\Gateway\DoctrineDatabase($connection),
            new User\Mapper(),
            new LimitationConverter([new ObjectStateLimitationHandler($connection)])
        );
    }

    protected function getValidUser()
    {
        $user = new Persistence\User();
        $user->id = self::TEST_USER_ID;
        $user->login = 'kore';
        $user->email = 'kore@example.org';
        $user->passwordHash = '1234567890';
        $user->hashAlgorithm = 2;
        $user->isEnabled = true;
        $user->maxLogin = 23;
        $user->passwordUpdatedAt = 1569229200;

        return $user;
    }

    protected function getValidUserToken($time = null)
    {
        $userToken = new Persistence\User\UserTokenUpdateStruct();
        $userToken->userId = self::TEST_USER_ID;
        $userToken->hashKey = md5('hash');
        $userToken->time = $time ?? (new DateTime())->add(new DateInterval('P1D'))->getTimestamp();

        return $userToken;
    }

    public function testCreateUser()
    {
        $handler = $this->getUserHandler();

        $this->expectException(NotImplementedException::class);
        $handler->create($this->getValidUser());
    }

    protected function getGatewayReturnValue(): array
    {
        return [
            $this->getDummyUser(
                self::TEST_USER_ID,
                'kore',
                'kore@example.org'
            ),
        ];
    }

    protected function getDummyUser(
        int $id,
        string $login,
        string $email
    ): array {
        return [
            'contentobject_id' => $id,
            'login' => $login,
            'email' => $email,
            'password_hash' => '1234567890',
            'password_hash_type' => 2,
            'is_enabled' => true,
            'max_login' => 23,
            'password_updated_at' => 1569229200,
        ];
    }

    public function testLoadUser()
    {
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('load')
            ->with(self::TEST_USER_ID)
            ->willReturn($this->getGatewayReturnValue());

        $handler = $this->getUserHandler($gatewayMock);

        $user = $this->getValidUser();

        $this->assertEquals(
            $user,
            $handler->load($user->id)
        );
    }

    public function testLoadUnknownUser()
    {
        $this->expectException(NotFoundException::class);
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('load')
            ->with(1337)
            ->willReturn([]);

        $handler = $this->getUserHandler($gatewayMock);

        $handler->load(1337);
    }

    public function testLoadUserByLogin()
    {
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('loadByLogin')
            ->with('kore')
            ->willReturn($this->getGatewayReturnValue());

        $handler = $this->getUserHandler($gatewayMock);
        $user = $this->getValidUser();

        $loadedUser = $handler->loadByLogin($user->login);
        $this->assertEquals(
            $user,
            $loadedUser
        );
    }

    public function testLoadMultipleUsersByLogin()
    {
        $this->expectException(LogicException::class);

        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('loadByLogin')
            ->with('kore')
            ->willReturn([
                $this->getDummyUser(self::TEST_USER_ID, 'kore', 'kore@example.org'),
                $this->getDummyUser(self::TEST_USER_ID + 1, 'kore', 'kore@example.org'),
            ]);

        $handler = $this->getUserHandler($gatewayMock);
        $user = $this->getValidUser();

        $handler->loadByLogin($user->login);
    }

    public function testLoadMultipleUsersByEmail()
    {
        $this->expectException(LogicException::class);

        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('loadByEmail')
            ->with('kore@example.org')
            ->willReturn([
                $this->getDummyUser(self::TEST_USER_ID, 'kore_a', 'kore@example.org'),
                $this->getDummyUser(self::TEST_USER_ID + 1, 'kore_b', 'kore@example.org'),
            ]);

        $handler = $this->getUserHandler($gatewayMock);
        $user = $this->getValidUser();

        $handler->loadByEmail($user->email);
    }

    public function testLoadUserByEmailNotFound()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getUserHandler();
        $user = $this->getValidUser();

        $handler->loadByLogin($user->email);
    }

    public function testLoadUserByEmail()
    {
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('loadByEmail')
            ->with('kore@example.org')
            ->willReturn($this->getGatewayReturnValue());

        $handler = $this->getUserHandler($gatewayMock);
        $validUser = $this->getValidUser();

        $user = $handler->loadByEmail($validUser->email);
        $this->assertEquals(
            $validUser,
            $user
        );
    }

    public function testLoadUsersByEmail()
    {
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $gatewayMock
            ->method('loadByEmail')
            ->with('kore@example.org')
            ->willReturn($this->getGatewayReturnValue());

        $handler = $this->getUserHandler($gatewayMock);
        $user = $this->getValidUser();

        $users = $handler->loadUsersByEmail($user->email);
        $this->assertEquals(
            $user,
            $users[0]
        );
    }

    public function testLoadUserByTokenNotFound()
    {
        $this->expectException(NotFoundException::class);

        $handler = $this->getUserHandler();
        $handler->updateUserToken($this->getValidUserToken());

        $handler->loadUserByToken('asd');
    }

    public function testLoadUserByToken()
    {
        $gatewayMock = $this
            ->createMock(User\Gateway::class);

        $userToken = $this->getValidUserToken();
        $gatewayMock
            ->method('loadUserByToken')
            ->with($userToken->hashKey)
            ->willReturn($this->getGatewayReturnValue());

        $handler = $this->getUserHandler($gatewayMock);
        $user = $this->getValidUser();
        $handler->updateUserToken($userToken);

        $loadedUser = $handler->loadUserByToken($userToken->hashKey);
        $this->assertEquals(
            $user,
            $loadedUser
        );
    }

    public function testUpdateUserToken()
    {
        $handler = $this->getUserHandler();

        $handler->updateUserToken($this->getValidUserToken(1234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 1234567890, self::TEST_USER_ID]],
            $this->getDatabaseConnection()->createQueryBuilder()->select(
                ['hash_key', 'id', 'time', 'user_id']
            )->from('ezuser_accountkey'),
            'Expected user data to be updated.'
        );

        $handler->updateUserToken($this->getValidUserToken(2234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 2234567890, self::TEST_USER_ID]],
            $this->getDatabaseConnection()->createQueryBuilder()->select(
                ['hash_key', 'id', 'time', 'user_id']
            )->from('ezuser_accountkey'),
            'Expected user token data to be updated.'
        );
    }

    public function testExpireUserToken()
    {
        $handler = $this->getUserHandler();

        $handler->updateUserToken($userToken = $this->getValidUserToken(1234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 1234567890, self::TEST_USER_ID]],
            $this->getDatabaseConnection()->createQueryBuilder()->select(
                ['hash_key', 'id', 'time', 'user_id']
            )->from('ezuser_accountkey'),
            'Expected user data to be updated.'
        );

        $handler->expireUserToken($userToken->hashKey);

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 0, self::TEST_USER_ID]],
            $this->getDatabaseConnection()->createQueryBuilder()->select(
                ['hash_key', 'id', 'time', 'user_id']
            )->from('ezuser_accountkey'),
            'Expected user token to be expired.'
        );
    }

    public function testDeleteNonExistingUser()
    {
        $handler = $this->getUserHandler();

        $this->expectException(NotImplementedException::class);
        $handler->delete(1337);
    }

    public function testUpdateUser()
    {
        $handler = $this->getUserHandler();
        $user = $this->getValidUser();

        $user->login = 'New_lögin';
        $this->expectException(NotImplementedException::class);
        $handler->update($user);
    }

    public function testUpdateUserSettings()
    {
        $handler = $this->getUserHandler();
        $user = $this->getValidUser();

        $user->maxLogin = 42;
        $this->expectException(NotImplementedException::class);
        $handler->update($user);
    }

    public function testCreateNewRoleWithoutPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $handler->createRole($createStruct);

        $this->assertQueryResult(
            [[1, 'Test', -1]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'name', 'version')->from('ezrole'),
            'Expected a new role draft.'
        );
    }

    public function testCreateRoleDraftWithoutPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);

        $handler->createRoleDraft($roleDraft->id);

        $publishedRoleId = 1;
        $this->assertQueryResult(
            [
                [$publishedRoleId, 'Test', APIRole::STATUS_DEFINED],
                [2, 'Test', $publishedRoleId],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'name', 'version')->from('ezrole'),
            'Expected a role and a role draft.'
        );
    }

    public function testCreateNewRoleRoleId()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $this->assertSame(1, $roleDraft->id);
    }

    public function testLoadRole()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $this->assertEquals(
            $roleDraft->id,
            $role->id
        );
    }

    public function testLoadRoleWithPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);
        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoleWithPoliciesAndGroups()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);

        $handler->assignRole(23, $roleDraft->id);
        $handler->assignRole(42, $roleDraft->id);

        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoleWithPolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);
        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => [
                            'Subtree' => ['/1', '/1/2'],
                            'Foo' => ['Bar'],
                        ],
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoles()
    {
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [],
            $handler->loadRoles()
        );

        $role = $this->createTestRole($handler);

        $this->assertEquals(
            [$role],
            $handler->loadRoles()
        );
    }

    public function testUpdateRole()
    {
        $handler = $this->getUserHandler();

        $role = $this->createTestRole($handler);

        $update = new Persistence\User\RoleUpdateStruct();
        $update->id = $role->id;
        $update->identifier = 'Changed';

        $handler->updateRole($update);

        $this->assertQueryResult(
            [[1, 'Changed']],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'name')->from('ezrole'),
            'Expected a changed role.'
        );
    }

    public function testDeleteRole()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $handler->deleteRole(3);

        $this->assertQueryResult(
            [],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id')->from('ezrole')->where('id = 3'),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            [],
            $this->getDatabaseConnection()->createQueryBuilder()->select('role_id')->from('ezpolicy')->where('role_id = 3'),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            [],
            $this->getDatabaseConnection()->createQueryBuilder()->select('role_id')->from('ezuser_role')->where('role_id = 3'),
            'Expected an empty set.'
        );
    }

    public function testDeleteRoleDraft()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $roleDraft = $handler->createRoleDraft(3);
        $handler->deleteRole($roleDraft->id, APIRole::STATUS_DRAFT);

        $this->assertQueryResult(
            [['3', APIRole::STATUS_DEFINED]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id, version')->from('ezrole')->where('id = 3'),
            'Expected a published role.'
        );

        $this->assertQueryResult(
            [[implode("\n", array_fill(0, 28, '3, ' . APIRole::STATUS_DEFINED))]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('role_id, original_id')->from('ezpolicy')->where('role_id = 3'),
            'Expected 28 policies for the published role.'
        );

        $this->assertQueryResult(
            [[3], [3]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('role_id')->from('ezuser_role')->where('role_id = 3'),
            'Expected that role assignments still exist.'
        );
    }

    public function testAddPolicyToRoleLimitations()
    {
        $handler = $this->getUserHandler();

        $role = $this->createTestRole($handler);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicy($role->id, $policy);

        $this->assertQueryResult(
            [[1, 'foo', 'bar', 1]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy'),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyPolicyId()
    {
        $handler = $this->getUserHandler();

        $role = $this->createTestRole($handler);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $policy = $handler->addPolicy($role->id, $policy);

        $this->assertEquals(1, $policy->id);
    }

    public function testAddPolicyLimitations()
    {
        $this->createTestRoleWithTestPolicy();

        $this->assertQueryResult(
            [
                [1, 'Subtree', 1],
                [2, 'Foo', 1],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'identifier', 'policy_id')->from('ezpolicy_limitation'),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyLimitationValues()
    {
        $this->createTestRoleWithTestPolicy();

        $this->assertQueryResult(
            [
                [1, '/1', 1],
                [2, '/1/2', 1],
                [3, 'Bar', 2],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'value', 'limitation_id')->from('ezpolicy_limitation_value'),
            'Expected a new policy.'
        );
    }

    protected function createRole()
    {
        $handler = $this->getUserHandler();

        $policy1 = new Persistence\User\Policy();
        $policy1->module = 'foo';
        $policy1->function = 'bar';
        $policy1->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $policy2 = new Persistence\User\Policy();
        $policy2->module = 'foo';
        $policy2->function = 'blubb';
        $policy2->limitations = [
            'Foo' => ['Blubb'],
        ];

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';
        $createStruct->policies = [$policy1, $policy2];

        return $handler->createRole($createStruct);
    }

    public function testImplicitlyCreatePolicies()
    {
        $this->createRole();

        $this->assertQueryResult(
            [
                [1, 'foo', 'bar', 1],
                [2, 'foo', 'blubb', 1],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy'),
            'Expected a new policy.'
        );
    }

    public function testDeletePolicy()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [
                [2, 'foo', 'blubb', 1],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy')->where('original_id = 0'),
            'Expected a new policy.'
        );
    }

    public function testDeletePolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [[3, 'Foo', 2]],
            $this->getDatabaseConnection()->createQueryBuilder()->select('*')->from('ezpolicy_limitation')
        );
    }

    public function testDeletePolicyLimitationValues()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [[4, 3, 'Blubb']],
            $this->getDatabaseConnection()->createQueryBuilder()->select('*')->from('ezpolicy_limitation_value')
        );
    }

    public function testUpdatePolicies()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();

        $policy = $roleDraft->policies[0];
        $policy->limitations = [
            'new' => ['something'],
        ];

        $handler->updatePolicy($policy);

        $this->assertQueryResult(
            [
                [3, 'Foo', 2],
                [4, 'new', 1],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('*')->from('ezpolicy_limitation')
        );

        $this->assertQueryResult(
            [
                [4, 3, 'Blubb'],
                [5, 4, 'something'],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('*')->from('ezpolicy_limitation_value')
        );
    }

    public function testAddRoleToUser()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $user = $this->getValidUser();

        $handler->assignRole($user->id, $role->id, []);

        $this->assertQueryResult(
            [
                [1, self::TEST_USER_ID, 1, null, null],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithLimitation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $user = $this->getValidUser();

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1'],
            ]
        );

        $this->assertQueryResult(
            [
                [1, self::TEST_USER_ID, 1, 'Subtree', '/1'],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithComplexLimitation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $user = $this->getValidUser();

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1', '/1/2'],
                'Foo' => ['Bar'],
            ]
        );

        $this->assertQueryResult(
            [
                [1, self::TEST_USER_ID, 1, 'Subtree', '/1'],
                [2, self::TEST_USER_ID, 1, 'Subtree', '/1/2'],
                [3, self::TEST_USER_ID, 1, 'Foo', 'Bar'],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testRemoveUserRoleAssociation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $user = $this->getValidUser();

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1', '/1/2'],
                'Foo' => ['Bar'],
            ]
        );

        $handler->unassignRole($user->id, $role->id);

        $this->assertQueryResult(
            [],
            $this->getDatabaseConnection()->createQueryBuilder()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected no user policy associations.'
        );
    }

    public function testLoadPoliciesForUser()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        $policies = $handler->loadPoliciesByUserId(10); // Anonymous user

        // Verify, that we received an array of Policy objects
        $this->assertTrue(
            array_reduce(
                array_map(
                    function ($policy) {
                        return $policy instanceof Persistence\User\Policy;
                    },
                    $policies
                ),
                function ($a, $b) {
                    return $a && $b;
                },
                true
            )
        );
        $this->assertCount(8, $policies);
    }

    public function testLoadRoleAssignmentsByGroupId()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 28,
                        'roleId' => 1,
                        'contentId' => 11,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 34,
                        'roleId' => 5,
                        'contentId' => 11,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(11)// 11: Members
        );

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(42)// 42: Anonymous Users
        );

        $this->assertEquals(
            [],
            $handler->loadRoleAssignmentsByGroupId(10)// 10: Anonymous User
        );
    }

    public function testLoadRoleAssignmentsByGroupIdInherited()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(10, true)// 10: Anonymous User
        );
    }

    public function testLoadComplexRoleAssignments()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 32,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/2/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 33,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/43/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 38,
                        'roleId' => 5,
                        'contentId' => 13,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(13)
        );

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 32,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/2/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 33,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/43/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 38,
                        'roleId' => 5,
                        'contentId' => 13,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(13, true)
        );
    }

    public function testLoadRoleAssignmentsByRoleId()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 28,
                        'roleId' => 1,
                        'contentId' => 11,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 37,
                        'roleId' => 1,
                        'contentId' => 59,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByRoleId(1)
        );
    }

    public function testLoadRoleDraftByRoleId()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $originalRoleId = 3;
        $draft = $handler->createRoleDraft($originalRoleId);
        $loadedDraft = $handler->loadRoleDraftByRoleId($originalRoleId);
        self::assertSame($loadedDraft->originalId, $originalRoleId);
        self::assertEquals($draft, $loadedDraft);
    }

    public function testRoleDraftOnlyHavePolicyDraft()
    {
        $this->insertSharedDatabaseFixture();
        $handler = $this->getUserHandler();
        $originalRoleId = 3;
        $originalRole = $handler->loadRole($originalRoleId);
        $originalPolicies = [];
        foreach ($originalRole->policies as $policy) {
            $originalPolicies[$policy->id] = $policy;
        }

        $draft = $handler->createRoleDraft($originalRoleId);
        $loadedDraft = $handler->loadRole($draft->id, Role::STATUS_DRAFT);
        self::assertSame($loadedDraft->originalId, $originalRoleId);
        self::assertEquals($draft, $loadedDraft);
        foreach ($loadedDraft->policies as $policy) {
            self::assertTrue(isset($originalPolicies[$policy->originalId]));
        }

        // Now add a new policy. Original ID of the new one must be the same as its actual ID.
        $newPolicyModule = 'foo';
        $newPolicyFunction = 'bar';
        $policy = new Persistence\User\Policy(['module' => $newPolicyModule, 'function' => $newPolicyFunction]);
        $policyDraft = $handler->addPolicyByRoleDraft($loadedDraft->id, $policy);

        // Test again by reloading the draft.
        $loadedDraft = $handler->loadRole($draft->id, Role::STATUS_DRAFT);
        foreach ($loadedDraft->policies as $policy) {
            if ($policy->id != $policyDraft->id) {
                continue;
            }

            self::assertNotNull($policy->originalId);
            self::assertSame($policy->id, $policy->originalId);
        }
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function createTestRole(User\Handler $handler): Role
    {
        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);

        return $handler->loadRole($roleDraft->id);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function createTestRoleWithTestPolicy(): void
    {
        $handler = $this->getUserHandler();

        $role = $this->createTestRole($handler);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $handler->addPolicy($role->id, $policy);
    }
}
