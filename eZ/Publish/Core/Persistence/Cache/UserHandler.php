<?php

/**
 * File containing a User Handler impl.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Cache handler for user module.
 */
class UserHandler extends AbstractHandler implements UserHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(User $user)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->create($user);

        // Clear corresponding content cache as creation of the User changes it's external data
        $this->cache->clear('content', $user->id);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->userHandler()->load($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLogin($login)
    {
        $this->logger->logCall(__METHOD__, array('user' => $login));

        return $this->persistenceHandler->userHandler()->loadByLogin($login);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEmail($email)
    {
        $this->logger->logCall(__METHOD__, array('email' => $email));

        return $this->persistenceHandler->userHandler()->loadByEmail($email);
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->update($user);

        // Clear corresponding content cache as update of the User changes it's external data
        $this->cache->clear('content', $user->id);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));
        $return = $this->persistenceHandler->userHandler()->delete($userId);

        // user id == content id == group id
        $this->cache->clear('content', $userId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $userId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited', $userId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function createRole(RoleCreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $createStruct));

        return $this->persistenceHandler->userHandler()->createRole($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->createRoleDraft($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        if ($status === Role::STATUS_DEFINED) {
            $cache = $this->cache->getItem('user', 'role', $roleId);
            $role = $cache->get();
            if ($cache->isMiss()) {
                $this->logger->logCall(__METHOD__, array('role' => $roleId));
                $role = $this->persistenceHandler->userHandler()->loadRole($roleId, $status);
                $cache->set($role)->save();
            }
        } else {
            $role = $this->persistenceHandler->userHandler()->loadRole($roleId, $status);
        }

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED)
    {
        $this->logger->logCall(__METHOD__, array('role' => $identifier));

        return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->loadRoleDraftByRoleId($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoles()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->userHandler()->loadRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        $this->logger->logCall(__METHOD__, array('assignment' => $roleAssignmentId));

        return $this->persistenceHandler->userHandler()->loadRoleAssignment($roleAssignmentId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignmentsByGroupId($groupId, $inherit = false)
    {
        if ($inherit) {
            $cache = $this->cache->getItem('user', 'role', 'assignments', 'byGroup', 'inherited', $groupId);
        } else {
            $cache = $this->cache->getItem('user', 'role', 'assignments', 'byGroup', $groupId);
        }
        $assignments = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('group' => $groupId, 'inherit' => $inherit));
            $assignments = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId(
                $groupId,
                $inherit
            );
            $cache->set($assignments)->save();
        }

        return $assignments;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRole(RoleUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $this->persistenceHandler->userHandler()->updateRole($struct);

        $this->cache->clear('user', 'role', $struct->id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId, $status);

        if ($status === Role::STATUS_DEFINED) {
            $this->cache->clear('user', 'role', $roleId);
            $this->cache->clear('user', 'role', 'assignments');
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function publishRoleDraft($roleDraftId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleDraftId));
        $userHandler = $this->persistenceHandler->userHandler();
        $roleDraft = $userHandler->loadRole($roleDraftId, Role::STATUS_DRAFT);
        $return = $userHandler->publishRoleDraft($roleDraftId);

        $this->cache->clear('user', 'role', 'assignments');
        // Get right published role to cache it.
        try {
            // Role draft created from existing role.
            $publishedRole = $userHandler->loadRole($roleDraft->originalId);
        } catch (NotFoundException $e) {
            // Completely new role.
            $publishedRole = $userHandler->loadRole($roleDraftId);
        }
        $this->cache
            ->getItem('user', 'role', $publishedRole->id)
            ->set($publishedRole)
            ->save();

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicyByRoleDraft($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));

        return $this->persistenceHandler->userHandler()->addPolicyByRoleDraft($roleId, $policy);
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->addPolicy($roleId, $policy);

        $this->cache->clear('user', 'role', $roleId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->clear('user', 'role', $policy->roleId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePolicy($policyId, $roleId)
    {
        $this->logger->logCall(__METHOD__, array('policy' => $policyId));
        $this->persistenceHandler->userHandler()->deletePolicy($policyId, $roleId);

        $this->cache->clear('user', 'role');
    }

    /**
     * {@inheritdoc}
     */
    public function loadPoliciesByUserId($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->userHandler()->loadPoliciesByUserId($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function assignRole($contentId, $roleId, array $limitation = null)
    {
        $this->logger->logCall(__METHOD__, array('group' => $contentId, 'role' => $roleId, 'limitation' => $limitation));
        $return = $this->persistenceHandler->userHandler()->assignRole($contentId, $roleId, $limitation);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited');

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function unassignRole($contentId, $roleId)
    {
        $this->logger->logCall(__METHOD__, array('group' => $contentId, 'role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->unassignRole($contentId, $roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited');

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoleAssignment($roleAssignmentId)
    {
        $this->logger->logCall(__METHOD__, array('assignment' => $roleAssignmentId));
        $return = $this->persistenceHandler->userHandler()->removeRoleAssignment($roleAssignmentId);

        // We don't know the contentId, so clear all assignment cache.
        $this->cache->clear('user', 'role', 'assignments'); //TIMBER!

        return $return;
    }
}
