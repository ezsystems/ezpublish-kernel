<?php

/**
 * File containing the RoleServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Exception;

/**
 * Test case for operations in the RoleService using in memory storage.
 *
 * The following IDs from the default eZ community edition database are used in
 * this test:
 *
 * <ul>
 *   <li>
 *     ContentType
 *     <ul>
 *       <li><strong>28</strong>: File</li>
 *       <li><strong>29</strong>: Flash</li>
 *       <li><strong>30</strong>: Image</li>
 *     </ul>
 *   </li>
 * <ul>
 *
 * @see eZ\Publish\API\Repository\RoleService
 * @group role
 */
class RoleServiceTest extends BaseTest
{
    /**
     * Test for the newRoleCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct()
     */
    public function testNewRoleCreateStruct()
    {
        $repository = $this->getRepository();

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct', $roleCreate);
    }

    /**
     * Test for the newRoleCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testNewRoleCreateStructSetsNamePropertyOnStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        /* END: Use Case */

        $this->assertEquals('roleName', $roleCreate->identifier);
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole($roleCreate);

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleDraft',
            $role
        );

        return [
            'createStruct' => $roleCreate,
            'role' => $role,
        ];
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testRoleCreateStructValues(array $data)
    {
        $createStruct = $data['createStruct'];
        $role = $data['role'];

        $this->assertEquals(
            [
                'identifier' => $createStruct->identifier,
                'policies' => $createStruct->policies,
            ],
            [
                'identifier' => $role->identifier,
                'policies' => $role->policies,
            ]
        );
        $this->assertNotNull($role->id);

        return $data;
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRoleWithPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Create new subtree limitation
        $limitation = new SubtreeLimitation(
            [
                'limitationValues' => ['/1/2/'],
            ]
        );

        // Create policy create struct and add limitation to it
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'read');
        $policyCreate->addLimitation($limitation);

        // Add policy create struct to role create struct
        $roleCreate->addPolicy($policyCreate);

        $role = $roleService->createRole($roleCreate);

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleDraft',
            $role
        );

        return [
            'createStruct' => $roleCreate,
            'role' => $role,
        ];
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithPolicy
     */
    public function testRoleCreateStructValuesWithPolicy(array $data)
    {
        $createStruct = $data['createStruct'];
        $role = $data['role'];

        $this->assertEquals(
            [
                'identifier' => $createStruct->identifier,
                'policy_module' => $createStruct->policies[0]->module,
                'policy_function' => $createStruct->policies[0]->function,
                'policy_limitation' => array_values($createStruct->policies[0]->limitations),
            ],
            [
                'identifier' => $role->identifier,
                'policy_module' => $role->policies[0]->module,
                'policy_function' => $role->policies[0]->function,
                'policy_limitation' => array_values($role->policies[0]->limitations),
            ]
        );
        $this->assertNotNull($role->id);

        return $data;
    }

    /**
     * Test creating a role with multiple policies.
     *
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleWithMultiplePolicies()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        $limitation1 = new Limitation\ContentTypeLimitation();
        $limitation1->limitationValues = ['1', '3', '13'];

        $limitation2 = new Limitation\SectionLimitation();
        $limitation2->limitationValues = ['2', '3'];

        $limitation3 = new Limitation\OwnerLimitation();
        $limitation3->limitationValues = ['1', '2'];

        $limitation4 = new Limitation\UserGroupLimitation();
        $limitation4->limitationValues = ['1'];

        $policyCreateStruct1 = $roleService->newPolicyCreateStruct('content', 'read');
        $policyCreateStruct1->addLimitation($limitation1);
        $policyCreateStruct1->addLimitation($limitation2);

        $policyCreateStruct2 = $roleService->newPolicyCreateStruct('content', 'edit');
        $policyCreateStruct2->addLimitation($limitation3);
        $policyCreateStruct2->addLimitation($limitation4);

        $roleCreateStruct = $roleService->newRoleCreateStruct('ultimate_permissions');
        $roleCreateStruct->addPolicy($policyCreateStruct1);
        $roleCreateStruct->addPolicy($policyCreateStruct2);

        $createdRole = $roleService->createRole($roleCreateStruct);

        self::assertInstanceOf(Role::class, $createdRole);
        self::assertGreaterThan(0, $createdRole->id);

        $this->assertPropertiesCorrect(
            [
                'identifier' => $roleCreateStruct->identifier,
            ],
            $createdRole
        );

        self::assertCount(2, $createdRole->getPolicies());

        foreach ($createdRole->getPolicies() as $policy) {
            self::assertInstanceOf(Policy::class, $policy);
            self::assertGreaterThan(0, $policy->id);
            self::assertEquals($createdRole->id, $policy->roleId);

            self::assertCount(2, $policy->getLimitations());

            foreach ($policy->getLimitations() as $limitation) {
                self::assertInstanceOf(Limitation::class, $limitation);

                if ($policy->module == 'content' && $policy->function == 'read') {
                    switch ($limitation->getIdentifier()) {
                        case Limitation::CONTENTTYPE:
                            self::assertEquals($limitation1->limitationValues, $limitation->limitationValues);
                            break;

                        case Limitation::SECTION:
                            self::assertEquals($limitation2->limitationValues, $limitation->limitationValues);
                            break;

                        default:
                            self::fail('Created role contains limitations not defined with create struct');
                    }
                } elseif ($policy->module == 'content' && $policy->function == 'edit') {
                    switch ($limitation->getIdentifier()) {
                        case Limitation::OWNER:
                            self::assertEquals($limitation3->limitationValues, $limitation->limitationValues);
                            break;

                        case Limitation::USERGROUP:
                            self::assertEquals($limitation4->limitationValues, $limitation->limitationValues);
                            break;

                        default:
                            self::fail('Created role contains limitations not defined with create struct');
                    }
                } else {
                    self::fail('Created role contains policy not defined with create struct');
                }
            }
        }
    }

    /**
     * Test for the createRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);
        $newRoleDraft = $roleService->createRoleDraft($role);

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleDraft',
            $newRoleDraft
        );
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testCreateRoleThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('Editor');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // This call will fail with an InvalidArgumentException, because Editor exists
        $roleService->createRole($roleCreate);

        /* END: Use Case */
    }

    /**
     * Test for the createRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     */
    public function testCreateRoleDraftThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('Editor');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);
        $roleService->createRoleDraft($role); // First role draft

        // This call will fail with an InvalidArgumentException, because there is already a draft
        $roleService->createRoleDraft($role);

        /* END: Use Case */
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     */
    public function testCreateRoleThrowsLimitationValidationException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\LimitationValidationException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Create new role create struct
        $roleCreate = $roleService->newRoleCreateStruct('Lumberjack');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Create new subtree limitation
        $limitation = new SubtreeLimitation(
            [
                'limitationValues' => ['/mountain/forest/tree/42/'],
            ]
        );

        // Create policy create struct and add limitation to it
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'remove');
        $policyCreate->addLimitation($limitation);

        // Add policy create struct to role create struct
        $roleCreate->addPolicy($policyCreate);

        // This call will fail with an LimitationValidationException, because subtree
        // "/mountain/forest/tree/42/" does not exist
        $roleService->createRole($roleCreate);
        /* END: Use Case */
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRoleInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        $repository->beginTransaction();

        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $createdRoleId = $roleService->createRole($roleCreate)->id;

        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $role = $roleService->loadRole($createdRoleId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Role object still exists after rollback.');
    }

    /**
     * Test for the createRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRoleDraftInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        $repository->beginTransaction();

        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $createdRoleId = $roleService->createRole($roleCreate)->id;

        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $role = $roleService->loadRoleDraft($createdRoleId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Role draft object still exists after rollback.');
    }

    /**
     * Test for the loadRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);

        // Load the newly created role by its ID
        $role = $roleService->loadRole($roleDraft->id);

        /* END: Use Case */

        $this->assertEquals('roleName', $role->identifier);
    }

    /**
     * Test for the loadRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     */
    public function testLoadRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        // Load the newly created role by its ID
        $role = $roleService->loadRoleDraft($roleDraft->id);

        /* END: Use Case */

        $this->assertEquals('roleName', $role->identifier);
    }

    public function testLoadRoleDraftByRoleId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($role);

        // Now create a new draft based on the role
        $newDraft = $roleService->createRoleDraft($role);
        $loadedRoleDraft = $roleService->loadRoleDraftByRoleId($role->id);

        /* END: Use Case */

        self::assertEquals('roleName', $role->identifier);
        self::assertInstanceOf('eZ\Publish\API\Repository\Values\User\RoleDraft', $loadedRoleDraft);
        self::assertEquals($newDraft, $loadedRoleDraft);
    }

    /**
     * Test for the loadRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     */
    public function testLoadRoleThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();

        $nonExistingRoleId = $this->generateId('role', self::DB_INT_MAX);
        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRole($nonExistingRoleId);

        /* END: Use Case */
    }

    /**
     * Test for the loadRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleDraft
     */
    public function testLoadRoleDraftThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();

        $nonExistingRoleId = $this->generateId('role', self::DB_INT_MAX);
        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRoleDraft($nonExistingRoleId);

        /* END: Use Case */
    }

    public function testLoadRoleDraftByRoleIdThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();

        $nonExistingRoleId = $this->generateId('role', self::DB_INT_MAX);
        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRoleDraftByRoleId($nonExistingRoleId);

        /* END: Use Case */
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRoleByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);

        // Load the newly created role by its identifier
        $role = $roleService->loadRoleByIdentifier('roleName');

        /* END: Use Case */

        $this->assertEquals('roleName', $role->identifier);
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testLoadRoleByIdentifierThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRoleByIdentifier('MissingRole');

        /* END: Use Case */
    }

    /**
     * Test for the loadRoles() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRoles()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        // First create a custom role
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);

        // Now load all available roles
        $roles = $roleService->loadRoles();

        foreach ($roles as $role) {
            if ($role->identifier === 'roleName') {
                break;
            }
        }

        /* END: Use Case */

        $this->assertEquals('roleName', $role->identifier);
    }

    /**
     * Test for the loadRoles() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoles
     */
    public function testLoadRolesReturnsExpectedSetOfDefaultRoles()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roles = $roleService->loadRoles();

        $roleNames = [];
        foreach ($roles as $role) {
            $roleNames[] = $role->identifier;
        }
        /* END: Use Case */

        $this->assertEqualsCanonicalizing(
            [
                'Administrator',
                'Anonymous',
                'Editor',
                'Member',
                'Partner',
            ],
            $roleNames
        );
    }

    /**
     * Test for the newRoleUpdateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newRoleUpdateStruct()
     */
    public function testNewRoleUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleUpdate = $roleService->newRoleUpdateStruct('newRole');
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleUpdateStruct', $roleUpdate);
    }

    /**
     * Test for the updateRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::updateRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleDraft
     */
    public function testUpdateRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $roleUpdate = $roleService->newRoleUpdateStruct();
        $roleUpdate->identifier = 'updatedRole';

        $updatedRole = $roleService->updateRoleDraft($roleDraft, $roleUpdate);
        /* END: Use Case */

        // Now verify that our change was saved
        $role = $roleService->loadRoleDraft($updatedRole->id);

        $this->assertEquals($role->identifier, 'updatedRole');
    }

    /**
     * Test for the updateRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::updateRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRoleDraft
     */
    public function testUpdateRoleDraftThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $roleUpdate = $roleService->newRoleUpdateStruct();
        $roleUpdate->identifier = 'Editor';

        // This call will fail with an InvalidArgumentException, because Editor is a predefined role
        $roleService->updateRoleDraft($roleDraft, $roleUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the deleteRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::deleteRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoles
     */
    public function testDeleteRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        $roleService->deleteRole($role);
        /* END: Use Case */

        $this->assertCount(5, $roleService->loadRoles());
    }

    /**
     * Test for the deleteRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::deleteRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleDraft
     */
    public function testDeleteRoleDraft()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);
        $roleID = $roleDraft->id;
        $roleService->deleteRoleDraft($roleDraft);

        // This call will fail with a NotFoundException, because the draft no longer exists
        $roleService->loadRoleDraft($roleID);
        /* END: Use Case */
    }

    /**
     * Test for the newPolicyCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyCreateStruct()
     */
    public function testNewPolicyCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct', $policyCreate);
    }

    /**
     * Test for the newPolicyCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     */
    public function testNewPolicyCreateStructSetsStructProperties()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        /* END: Use Case */

        $this->assertEquals(
            ['content', 'create'],
            [$policyCreate->module, $policyCreate->function]
        );
    }

    /**
     * Test for the addPolicyByRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     */
    public function testAddPolicyByRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );
        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );
        /* END: Use Case */

        $actual = [];
        foreach ($roleDraft->getPolicies() as $policy) {
            $actual[] = [
                'module' => $policy->module,
                'function' => $policy->function,
            ];
        }
        usort(
            $actual,
            function ($p1, $p2) {
                return strcasecmp($p1['function'], $p2['function']);
            }
        );

        $this->assertEquals(
            [
                [
                    'module' => 'content',
                    'function' => 'create',
                ],
                [
                    'module' => 'content',
                    'function' => 'delete',
                ],
            ],
            $actual
        );
    }

    /**
     * Test for the addPolicyByRoleDraft() method.
     *
     * @return array [\eZ\Publish\API\Repository\Values\User\RoleDraft, \eZ\Publish\API\Repository\Values\User\Policy]
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraft
     */
    public function testAddPolicyByRoleDraftUpdatesRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $roleDraft = $roleService->addPolicyByRoleDraft($roleDraft, $policyCreate);

        $policy = null;
        foreach ($roleDraft->getPolicies() as $policy) {
            if ($policy->module === 'content' && $policy->function === 'create') {
                break;
            }
        }
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $policy
        );

        return [$roleDraft, $policy];
    }

    /**
     * Test for the addPolicyByRoleDraft() method.
     *
     * @param array $roleAndPolicy
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraftUpdatesRole
     */
    public function testAddPolicyByRoleDraftSetsPolicyProperties($roleAndPolicy)
    {
        list($role, $policy) = $roleAndPolicy;

        $this->assertEquals(
            [$role->id, 'content', 'create'],
            [$policy->roleId, $policy->module, $policy->function]
        );
    }

    /**
     * Test for the addPolicyByRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     */
    public function testAddPolicyByRoleDraftThrowsLimitationValidationException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\LimitationValidationException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct('Lumberjack');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        // Create new subtree limitation
        $limitation = new SubtreeLimitation(
            [
                'limitationValues' => ['/mountain/forest/tree/42/'],
            ]
        );

        // Create policy create struct and add limitation to it
        $policyCreateStruct = $roleService->newPolicyCreateStruct('content', 'remove');
        $policyCreateStruct->addLimitation($limitation);

        // This call will fail with an LimitationValidationException, because subtree
        // "/mountain/forest/tree/42/" does not exist
        $roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
        /* END: Use Case */
    }

    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraftUpdatesRole
     */
    public function testCreateRoleWithAddPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate a new create struct
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Add some role policies
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'read'
            )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'translate'
            )
        );

        // Create new role instance
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        $policies = [];
        foreach ($role->getPolicies() as $policy) {
            $policies[] = ['module' => $policy->module, 'function' => $policy->function];
        }
        /* END: Use Case */
        array_multisort($policies);

        $this->assertEquals(
            [
                [
                    'module' => 'content',
                    'function' => 'read',
                ],
                [
                    'module' => 'content',
                    'function' => 'translate',
                ],
            ],
            $policies
        );
    }

    /**
     * Test for the createRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraftUpdatesRole
     */
    public function testCreateRoleDraftWithAddPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate a new create struct
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Add some role policies
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'read'
            )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'translate'
            )
        );

        // Create new role instance
        $roleDraft = $roleService->createRole($roleCreate);

        $policies = [];
        foreach ($roleDraft->getPolicies() as $policy) {
            $policies[] = ['module' => $policy->module, 'function' => $policy->function];
        }
        /* END: Use Case */

        $this->assertEquals(
            [
                [
                    'module' => 'content',
                    'function' => 'read',
                ],
                [
                    'module' => 'content',
                    'function' => 'translate',
                ],
            ],
            $policies
        );
    }

    /**
     * Test for the newPolicyUpdateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyUpdateStruct()
     */
    public function testNewPolicyUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct',
            $policyUpdate
        );
    }

    public function testUpdatePolicyByRoleDraftNoLimitation()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate new policy create
        $policyCreate = $roleService->newPolicyCreateStruct('foo', 'bar');

        // Instantiate a role create and add the policy create
        $roleCreate = $roleService->newRoleCreateStruct('myRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy($policyCreate);

        // Create a new role instance.
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        $roleDraft = $roleService->createRoleDraft($role);
        // Search for the new policy instance
        $policy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        foreach ($roleDraft->getPolicies() as $policy) {
            if ($policy->module === 'foo' && $policy->function === 'bar') {
                break;
            }
        }

        // Create an update struct
        $policyUpdate = $roleService->newPolicyUpdateStruct();

        // Update the the policy
        $policy = $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $policy,
            $policyUpdate
        );
        $roleService->publishRoleDraft($roleDraft);

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $policy
        );

        self::assertEquals([], $policy->getLimitations());
    }

    /**
     * @return array
     *
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraft
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyUpdateStruct
     */
    public function testUpdatePolicyByRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate new policy create
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'translate');

        // Add some limitations for the new policy
        $policyCreate->addLimitation(
            new LanguageLimitation(
                [
                    'limitationValues' => ['eng-US', 'eng-GB'],
                ]
            )
        );

        // Instantiate a role create and add the policy create
        $roleCreate = $roleService->newRoleCreateStruct('myRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy($policyCreate);

        // Create a new role instance.
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        $roleDraft = $roleService->createRoleDraft($role);
        // Search for the new policy instance
        $policy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        foreach ($roleDraft->getPolicies() as $policy) {
            if ($policy->module === 'content' && $policy->function === 'translate') {
                break;
            }
        }

        // Create an update struct and set a modified limitation
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                [
                    'limitationValues' => [29, 30],
                ]
            )
        );

        // Update the the policy
        $policy = $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $policy,
            $policyUpdate
        );
        $roleService->publishRoleDraft($roleDraft);

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $policy
        );

        return [$roleService->loadRole($role->id), $policy];
    }

    /**
     * @param array $roleAndPolicy
     *
     * @see \eZ\Publish\API\Repository\RoleService::testUpdatePolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicyByRoleDraft
     */
    public function testUpdatePolicyUpdatesLimitations($roleAndPolicy)
    {
        list($role, $policy) = $roleAndPolicy;

        $this->assertEquals(
            [
                new ContentTypeLimitation(
                    [
                        'limitationValues' => [29, 30],
                    ]
                ),
            ],
            $policy->getLimitations()
        );

        return $role;
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicyUpdatesLimitations
     */
    public function testUpdatePolicyUpdatesRole($role)
    {
        $limitations = [];
        foreach ($role->getPolicies() as $policy) {
            foreach ($policy->getLimitations() as $limitation) {
                $limitations[] = $limitation;
            }
        }

        $this->assertCount(1, $limitations);
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation',
            $limitations[0]
        );

        $expectedData = [
            'limitationValues' => [29, 30],
        ];
        $this->assertPropertiesCorrectUnsorted(
            $expectedData,
            $limitations[0]
        );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraft
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testUpdatePolicyByRoleDraftThrowsLimitationValidationException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\LimitationValidationException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate new policy create
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'remove');

        // Add some limitations for the new policy
        $policyCreate->addLimitation(
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/2/'],
                ]
            )
        );

        // Instantiate a role create and add the policy create
        $roleCreate = $roleService->newRoleCreateStruct('myRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy($policyCreate);

        // Create a new role instance.
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);
        $roleDraft = $roleService->createRoleDraft($role);
        // Search for the new policy instance
        $policy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        foreach ($roleDraft->getPolicies() as $policy) {
            if ($policy->module === 'content' && $policy->function === 'remove') {
                break;
            }
        }

        // Create an update struct and set a modified limitation
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/mountain/forest/tree/42/'],
                ]
            )
        );

        // This call will fail with an LimitationValidationException, because subtree
        // "/mountain/forest/tree/42/" does not exist
        $policy = $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $policy,
            $policyUpdate
        );
        /* END: Use Case */
    }

    /**
     * Test for the removePolicyByRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::removePolicyByRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraft
     */
    public function testRemovePolicyByRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate a new role create
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Create a new role with two policies
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );
        $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );

        // Delete all policies from the new role
        foreach ($roleDraft->getPolicies() as $policy) {
            $roleDraft = $roleService->removePolicyByRoleDraft($roleDraft, $policy);
        }
        /* END: Use Case */

        $this->assertSame([], $roleDraft->getPolicies());
    }

    /**
     * Test for the addPolicyByRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicyByRoleDraft()
     */
    public function testAddPolicyWithRoleAssignment()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        /* Create new user group */
        $mainGroupId = $this->generateId('group', 4);
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', 'newUserGroup');
        $userGroup = $userService->createUserGroup($userGroupCreate, $parentUserGroup);

        /* Create Role */
        $roleCreate = $roleService->newRoleCreateStruct('newRole');
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);

        $role = $roleService->loadRole($roleDraft->id);
        $roleService->assignRoleToUserGroup($role, $userGroup);

        $roleAssignmentsBeforeNewPolicy = $roleService->getRoleAssignments($role)[0];

        /* Add new policy to existing role */
        $roleUpdateDraft = $roleService->createRoleDraft($role);
        $roleUpdateDraft = $roleService->addPolicyByRoleDraft(
            $roleUpdateDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );
        $roleService->publishRoleDraft($roleUpdateDraft);

        $roleAfterUpdate = $roleService->loadRole($role->id);
        $roleAssignmentsAfterNewPolicy = $roleService->getRoleAssignments($roleAfterUpdate)[0];
        /* END: Use Case */

        $this->assertNotEquals($roleAssignmentsBeforeNewPolicy->id, $roleAssignmentsAfterNewPolicy->id);
    }

    /**
     * Test loading user/group role assignments.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     *
     * @covers \eZ\Publish\API\Repository\RoleService::loadRoleAssignment
     */
    public function testLoadRoleAssignment()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $user = $repository->getUserService()->loadUser(14);

        // Check inital empty assigments (also warms up potential cache to validate it is correct below)
        $this->assertCount(0, $roleService->getRoleAssignmentsForUser($user));

        // Assignment to user group
        $groupRoleAssignment = $roleService->loadRoleAssignment(25);

        // Assignment to user
        $role = $roleService->loadRole(2);
        $roleService->assignRoleToUser($role, $user);
        $userRoleAssignments = $roleService->getRoleAssignmentsForUser($user);

        $userRoleAssignment = $roleService->loadRoleAssignment($userRoleAssignments[0]->id);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            $groupRoleAssignment
        );

        $this->assertEquals(
            [
                12,
                2,
                25,
            ],
            [
                $groupRoleAssignment->userGroup->id,
                $groupRoleAssignment->role->id,
                $groupRoleAssignment->id,
            ]
        );

        self::assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\UserRoleAssignment', $userRoleAssignment);
        self::assertEquals(14, $userRoleAssignment->user->id);

        return $groupRoleAssignment;
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testGetRoleAssignments()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Load the editor role
        $role = $roleService->loadRoleByIdentifier('Editor');

        // Load all assigned users and user groups
        $roleAssignments = $roleService->getRoleAssignments($role);

        /* END: Use Case */

        $this->assertCount(2, $roleAssignments);
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            $roleAssignments[0]
        );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            $roleAssignments[1]
        );

        return $roleAssignments;
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleAssignment[] $roleAssignments
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     */
    public function testGetRoleAssignmentsContainExpectedLimitation(array $roleAssignments)
    {
        $this->assertEquals(
            'Subtree',
            reset($roleAssignments)->limitation->getIdentifier()
        );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     */
    public function testAssignRoleToUser()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Administrator" role
        $role = $roleService->loadRoleByIdentifier('Administrator');

        // Assign the "Administrator" role to the newly created user
        $roleService->assignRoleToUser($role, $user);

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Administrator + Example User
        $this->assertCount(2, $roleAssignments);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     */
    public function testAssignRoleToUserWithRoleLimitation()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Assign the "Anonymous" role to the newly created user
        $roleService->assignRoleToUser(
            $role,
            $user,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            )
        );

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Members + Partners + Anonymous + Example User
        $this->assertCount(4, $roleAssignments);

        // Get the role limitation
        $roleLimitation = null;
        foreach ($roleAssignments as $roleAssignment) {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ($roleLimitation) {
                $this->assertInstanceOf(
                    '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserRoleAssignment',
                    $roleAssignment
                );
                break;
            }
        }

        $this->assertEquals(
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            ),
            $roleLimitation
        );

        // Test again to see values being merged
        $roleService->assignRoleToUser(
            $role,
            $user,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/', '/1/2/'],
                ]
            )
        );

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments($role);

        // Members + Partners + Anonymous + Example User
        $this->assertCount(5, $roleAssignments);

        // Get the role limitation
        $roleLimitations = [];
        foreach ($roleAssignments as $roleAssignment) {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ($roleLimitation) {
                $this->assertInstanceOf(
                    '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserRoleAssignment',
                    $roleAssignment
                );
                $roleLimitations[] = $roleLimitation;
            }
        }
        array_multisort($roleLimitations);

        $this->assertEquals(
            [
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/2/'],
                    ]
                ),
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/43/'],
                    ]
                ),
            ],
            $roleLimitations
        );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testAssignRoleToUserWithRoleLimitationThrowsLimitationValidationException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\LimitationValidationException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Get current user
        $permissionResolver = $this->getRepository()->getPermissionResolver();
        $userService = $repository->getUserService();
        $currentUser = $userService->loadUser($permissionResolver->getCurrentUserReference()->getUserId());

        // Assign the "Anonymous" role to the current user
        // This call will fail with an LimitationValidationException, because subtree "/lorem/ipsum/42/"
        // does not exists
        $roleService->assignRoleToUser(
            $role,
            $currentUser,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/lorem/ipsum/42/'],
                ]
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * Makes sure assigning role several times throws.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testAssignRoleToUserThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Get current user
        $permissionResolver = $this->getRepository()->getPermissionResolver();
        $userService = $repository->getUserService();
        $currentUser = $userService->loadUser($permissionResolver->getCurrentUserReference()->getUserId());

        // Assign the "Anonymous" role to the current user
        try {
            $roleService->assignRoleToUser(
                $role,
                $currentUser
            );
        } catch (Exception $e) {
            $this->fail('Got exception at first valid attempt to assign role');
        }

        // Re-Assign the "Anonymous" role to the current user
        // This call will fail with an InvalidArgumentException, because limitation is already assigned
        $roleService->assignRoleToUser(
            $role,
            $currentUser
        );
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * Makes sure assigning role several times with same limitations throws.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testAssignRoleToUserWithRoleLimitationThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Get current user
        $permissionResolver = $this->getRepository()->getPermissionResolver();
        $userService = $repository->getUserService();
        $currentUser = $userService->loadUser($permissionResolver->getCurrentUserReference()->getUserId());

        // Assign the "Anonymous" role to the current user
        try {
            $roleService->assignRoleToUser(
                $role,
                $currentUser,
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/43/', '/1/2/'],
                    ]
                )
            );
        } catch (Exception $e) {
            $this->fail('Got exception at first valid attempt to assign role');
        }

        // Re-Assign the "Anonymous" role to the current user
        // This call will fail with an InvalidArgumentException, because limitation is already assigned
        $roleService->assignRoleToUser(
            $role,
            $currentUser,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the removeRoleAssignment() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::removeRoleAssignment()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     */
    public function testRemoveRoleAssignment()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier('Member');

        // Assign the "Member" role to the newly created user
        $roleService->assignRoleToUser($role, $user);

        // Unassign user from role
        $roleAssignments = $roleService->getRoleAssignmentsForUser($user);
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->role->id === $role->id) {
                $roleService->removeRoleAssignment($roleAssignment);
            }
        }
        // The assignments array will not contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Members + Editors + Partners
        $this->assertCount(3, $roleAssignments);
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     */
    public function testGetRoleAssignmentsForUserDirect()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct('Example Role');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('user', 'login')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('content', 'read')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('content', 'edit')
        );

        // Create the new role instance
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        // Check inital empty assigments (also warms up potential cache to validate it is correct below)
        $this->assertCount(0, $roleService->getRoleAssignmentsForUser($user));
        $this->assertCount(0, $roleService->getRoleAssignments($role));

        // Assign role to new user
        $roleService->assignRoleToUser($role, $user);

        // Load the currently assigned role
        $roleAssignments = $roleService->getRoleAssignmentsForUser($user);
        /* END: Use Case */

        $this->assertCount(1, $roleAssignments);
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserRoleAssignment',
            reset($roleAssignments)
        );
        $this->assertCount(1, $roleService->getRoleAssignments($role));
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     */
    public function testGetRoleAssignmentsForUserEmpty()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $permissionResolver = $this->getRepository()->getPermissionResolver();
        $userService = $repository->getUserService();
        $adminUser = $userService->loadUser($permissionResolver->getCurrentUserReference()->getUserId());

        // Load the currently assigned role
        $roleAssignments = $roleService->getRoleAssignmentsForUser($adminUser);
        /* END: Use Case */

        $this->assertCount(0, $roleAssignments);
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     */
    public function testGetRoleAssignmentsForUserInherited()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $permissionResolver = $this->getRepository()->getPermissionResolver();
        $userService = $repository->getUserService();
        $adminUser = $userService->loadUser($permissionResolver->getCurrentUserReference()->getUserId());

        // Load the currently assigned role + inherited role assignments
        $roleAssignments = $roleService->getRoleAssignmentsForUser($adminUser, true);
        /* END: Use Case */

        $this->assertCount(1, $roleAssignments);
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            reset($roleAssignments)
        );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     */
    public function testAssignRoleToUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Administrator" role
        $role = $roleService->loadRoleByIdentifier('Administrator');

        // Assign the "Administrator" role to the newly created user group
        $roleService->assignRoleToUserGroup($role, $userGroup);

        // The assignments array will contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Administrator + Example Group
        $this->assertCount(2, $roleAssignments);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * Related issue: EZP-29113
     *
     * @covers \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     */
    public function testAssignRoleToUserGroupAffectsRoleAssignmentsForUser()
    {
        $roleService = $this->getRepository()->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();
        $user = $this->createUser('user', 'John', 'Doe', $userGroup);

        $initRoleAssignments = $roleService->getRoleAssignmentsForUser($user, true);

        // Load the existing "Administrator" role
        $role = $roleService->loadRoleByIdentifier('Administrator');

        // Assign the "Administrator" role to the newly created user group
        $roleService->assignRoleToUserGroup($role, $userGroup);

        $updatedRoleAssignments = $roleService->getRoleAssignmentsForUser($user, true);
        /* END: Use Case */

        $this->assertEmpty($initRoleAssignments);
        $this->assertCount(1, $updatedRoleAssignments);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testAssignRoleToUserGroupWithRoleLimitation()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Assign the "Anonymous" role to the newly created user group
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            )
        );

        // The assignments array will contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Members + Partners + Anonymous + Example Group
        $this->assertCount(4, $roleAssignments);

        // Get the role limitation
        $roleLimitation = null;
        foreach ($roleAssignments as $roleAssignment) {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ($roleLimitation) {
                break;
            }
        }

        $this->assertEquals(
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            ),
            $roleLimitation
        );

        // Test again to see values being merged
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/', '/1/2/'],
                ]
            )
        );

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments($role);

        // Members + Partners + Anonymous + Example User
        $this->assertCount(5, $roleAssignments);

        // Get the role limitation
        $roleLimitations = [];
        foreach ($roleAssignments as $roleAssignment) {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ($roleLimitation) {
                $this->assertInstanceOf(
                    '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
                    $roleAssignment
                );
                $roleLimitations[] = $roleLimitation;
            }
        }
        array_multisort($roleLimitations);

        $this->assertEquals(
            [
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/2/'],
                    ]
                ),
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/43/'],
                    ]
                ),
            ],
            $roleLimitations
        );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testAssignRoleToUserGroupWithRoleLimitationThrowsLimitationValidationException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\LimitationValidationException::class);

        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $userGroup = $userService->loadUserGroup($mainGroupId);

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Assign the "Anonymous" role to the newly created user group
        // This call will fail with an LimitationValidationException, because subtree "/lorem/ipsum/42/"
        // does not exists
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/lorem/ipsum/42/'],
                ]
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * Makes sure assigning role several times throws.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testAssignRoleToUserGroupThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $userGroup = $userService->loadUserGroup($mainGroupId);

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Assign the "Anonymous" role to the newly created user group
        try {
            $roleService->assignRoleToUserGroup(
                $role,
                $userGroup
            );
        } catch (Exception $e) {
            $this->fail('Got exception at first valid attempt to assign role');
        }

        // Re-Assign the "Anonymous" role to the newly created user group
        // This call will fail with an InvalidArgumentException, because role is already assigned
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup
        );
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * Makes sure assigning role several times with same limitations throws.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testAssignRoleToUserGroupWithRoleLimitationThrowsInvalidArgumentException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $userGroup = $userService->loadUserGroup($mainGroupId);

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier('Anonymous');

        // Assign the "Anonymous" role to the newly created user group
        try {
            $roleService->assignRoleToUserGroup(
                $role,
                $userGroup,
                new SubtreeLimitation(
                    [
                        'limitationValues' => ['/1/43/', '/1/2/'],
                    ]
                )
            );
        } catch (Exception $e) {
            $this->fail('Got exception at first valid attempt to assign role');
        }

        // Re-Assign the "Anonymous" role to the newly created user group
        // This call will fail with an InvalidArgumentException, because limitation is already assigned
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup,
            new SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/'],
                ]
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the removeRoleAssignment() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::removeRoleAssignment()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testRemoveRoleAssignmentFromUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier('Member');

        // Assign the "Member" role to the newly created user group
        $roleService->assignRoleToUserGroup($role, $userGroup);

        // Unassign group from role
        $roleAssignments = $roleService->getRoleAssignmentsForUserGroup($userGroup);

        // This call will fail with an "UnauthorizedException"
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->role->id === $role->id) {
                $roleService->removeRoleAssignment($roleAssignment);
            }
        }
        // The assignments array will not contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments($role);
        /* END: Use Case */

        // Members + Editors + Partners
        $this->assertCount(3, $roleAssignments);
    }

    /**
     * Test unassigning role by assignment.
     *
     * @covers \eZ\Publish\API\Repository\RoleService::removeRoleAssignment
     */
    public function testUnassignRoleByAssignment()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        $role = $roleService->loadRole(2);
        $user = $repository->getUserService()->loadUser(14);

        $originalAssignmentCount = count($roleService->getRoleAssignmentsForUser($user));

        $roleService->assignRoleToUser($role, $user);
        $newAssignmentCount = count($roleService->getRoleAssignmentsForUser($user));
        self::assertEquals($originalAssignmentCount + 1, $newAssignmentCount);

        $assignments = $roleService->getRoleAssignmentsForUser($user);
        $roleService->removeRoleAssignment($assignments[0]);
        $finalAssignmentCount = count($roleService->getRoleAssignmentsForUser($user));
        self::assertEquals($newAssignmentCount - 1, $finalAssignmentCount);
    }

    /**
     * Test unassigning role by assignment.
     *
     * But on current admin user so he lacks access to read roles.
     *
     * @covers \eZ\Publish\API\Repository\RoleService::removeRoleAssignment
     */
    public function testUnassignRoleByAssignmentThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        try {
            $adminUserGroup = $repository->getUserService()->loadUserGroup(12);
            $assignments = $roleService->getRoleAssignmentsForUserGroup($adminUserGroup);
            $roleService->removeRoleAssignment($assignments[0]);
        } catch (Exception $e) {
            self::fail(
                'Unexpected exception: ' . $e->getMessage() . " \n[" . $e->getFile() . ' (' . $e->getLine() . ')]'
            );
        }

        $roleService->removeRoleAssignment($assignments[0]);
    }

    /**
     * Test unassigning role by non-existing assignment.
     *
     * @covers \eZ\Publish\API\Repository\RoleService::removeRoleAssignment
     */
    public function testUnassignRoleByAssignmentThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        try {
            $editorsUserGroup = $repository->getUserService()->loadUserGroup(13);
            $assignments = $roleService->getRoleAssignmentsForUserGroup($editorsUserGroup);
            $roleService->removeRoleAssignment($assignments[0]);
        } catch (Exception $e) {
            self::fail(
                'Unexpected exception: ' . $e->getMessage() . " \n[" . $e->getFile() . ' (' . $e->getLine() . ')]'
            );
        }

        $roleService->removeRoleAssignment($assignments[0]);
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     */
    public function testGetRoleAssignmentsForUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct('Example Role');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('user', 'login')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('content', 'read')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('content', 'edit')
        );

        // Create the new role instance
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        // Assign role to new user group
        $roleService->assignRoleToUserGroup($role, $userGroup);

        // Load the currently assigned role
        $roleAssignments = $roleService->getRoleAssignmentsForUserGroup($userGroup);
        /* END: Use Case */

        $this->assertCount(1, $roleAssignments);
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            reset($roleAssignments)
        );
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testLoadPoliciesByUserId()
    {
        $repository = $this->getRepository();

        $anonUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonUserId is the ID of the "Anonymous" user.

        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        // Load "Anonymous" user
        $user = $userService->loadUser($anonUserId);

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct('User Role');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('notification', 'use')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('user', 'password')
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct('user', 'selfedit')
        );

        // Create the new role instance
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);

        // Assign role to anon user
        $roleService->assignRoleToUser($role, $user);
        $roleAssignments = $roleService->getRoleAssignmentsForUser($user, true);

        $policies = [];
        foreach ($roleAssignments as $roleAssignment) {
            $policies[] = $roleAssignment->getRole()->getPolicies();
        }
        $policies = array_merge(...$policies);

        $simplePolicyList = [];
        foreach ($policies as $simplePolicy) {
            $simplePolicyList[] = [$simplePolicy->roleId, $simplePolicy->module, $simplePolicy->function];
        }
        /* END: Use Case */
        array_multisort($simplePolicyList);

        $this->assertEquals(
            [
                [1, 'content', 'pdf'],
                [1, 'content', 'read'],
                [1, 'content', 'read'],
                [1, 'rss', 'feed'],
                [1, 'user', 'login'],
                [1, 'user', 'login'],
                [1, 'user', 'login'],
                [1, 'user', 'login'],
                [$role->id, 'notification', 'use'],
                [$role->id, 'user', 'password'],
                [$role->id, 'user', 'selfedit'],
            ],
            $simplePolicyList
        );
    }

    /**
     * Test for the publishRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::publishRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     */
    public function testPublishRoleDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );
        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );

        $roleService->publishRoleDraft($roleDraft);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Role',
            $roleService->loadRoleByIdentifier($roleCreate->identifier)
        );
    }

    /**
     * Test for the publishRoleDraft() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::publishRoleDraft()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleDraft
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyByRoleDraft
     */
    public function testPublishRoleDraftAddPolicies()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleDraft = $roleService->createRole($roleCreate);

        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );
        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );

        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRoleByIdentifier($roleCreate->identifier);
        /* END: Use Case */

        $actual = [];
        foreach ($role->getPolicies() as $policy) {
            $actual[] = [
                'module' => $policy->module,
                'function' => $policy->function,
            ];
        }
        usort(
            $actual,
            function ($p1, $p2) {
                return strcasecmp($p1['function'], $p2['function']);
            }
        );

        $this->assertEquals(
            [
                [
                    'module' => 'content',
                    'function' => 'create',
                ],
                [
                    'module' => 'content',
                    'function' => 'delete',
                ],
            ],
            $actual
        );
    }

    /**
     * Create a user group fixture in a variable named <b>$userGroup</b>,.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private function createUserGroupVersion1()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Inline */
        // $mainGroupId is the ID of the main "Users" group

        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', 'Example Group');

        // Create the new user group
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Inline */

        return $userGroup;
    }
}
