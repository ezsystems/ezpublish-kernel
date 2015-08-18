<?php

/**
 * File containing a User Handler impl.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Cache handler for user module.
 */
class UserHandler extends AbstractHandler implements UserHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::create
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
     * @see eZ\Publish\SPI\Persistence\User\Handler::load
     */
    public function load($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->userHandler()->load($userId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByLogin
     */
    public function loadByLogin($login)
    {
        $this->logger->logCall(__METHOD__, array('user' => $login));

        return $this->persistenceHandler->userHandler()->loadByLogin($login);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByEmail
     */
    public function loadByEmail($email)
    {
        $this->logger->logCall(__METHOD__, array('email' => $email));

        return $this->persistenceHandler->userHandler()->loadByEmail($email);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::update
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
     * @see eZ\Publish\SPI\Persistence\User\Handler::delete
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
     * @see eZ\Publish\SPI\Persistence\User\Handler::createRole
     */
    public function createRole(Role $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $role = $this->persistenceHandler->userHandler()->createRole($struct);

        // Don't cache draft roles
        if ($role->status == Role::STATUS_DEFINED) {
            $this->cache->getItem('user', 'role', $role->id)->set($role);
        }

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleDraft
     */
    public function loadRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->loadRoleDraft($roleId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRole
     */
    public function loadRole($roleId)
    {
        $cache = $this->cache->getItem('user', 'role', $roleId);
        $role = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('role' => $roleId));
            $role = $this->persistenceHandler->userHandler()->loadRole($roleId);
            $cache->set($role);
        }

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleDraftByIdentifier
     */
    public function loadRoleDraftByIdentifier($identifier)
    {
        $this->logger->logCall(__METHOD__, array('role' => $identifier));

        return $this->persistenceHandler->userHandler()->loadRoleDraftByIdentifier($identifier);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleByIdentifier
     */
    public function loadRoleByIdentifier($identifier)
    {
        $this->logger->logCall(__METHOD__, array('role' => $identifier));

        return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoles
     */
    public function loadRoles()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->userHandler()->loadRoles();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByRoleId
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByGroupId
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
            $cache->set($assignments);
        }

        return $assignments;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updateRoleDraft
     */
    public function updateRoleDraft(RoleUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        $this->persistenceHandler->userHandler()->updateRoleDraft($struct);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updateRole
     */
    public function updateRole(RoleUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $this->persistenceHandler->userHandler()->updateRole($struct);

        $this->cache->clear('user', 'role', $struct->id);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deleteRoleDraft
     */
    public function deleteRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->deleteRoleDraft($roleId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deleteRole
     */
    public function deleteRole($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments');

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::publishRoleDraft
     */
    public function publishRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->publishRoleDraft($roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments');

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicyByRoleDraft
     */
    public function addPolicyByRoleDraft($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));

        return $this->persistenceHandler->userHandler()->addPolicyByRoleDraft($roleId, $policy);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->addPolicy($roleId, $policy);

        $this->cache->clear('user', 'role', $roleId);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updatePolicy
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->clear('user', 'role', $policy->roleId);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deletePolicy
     */
    public function deletePolicy($policyId)
    {
        $this->logger->logCall(__METHOD__, array('policy' => $policyId));
        $this->persistenceHandler->userHandler()->deletePolicy($policyId);

        $this->cache->clear('user', 'role');
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadPoliciesByUserId
     */
    public function loadPoliciesByUserId($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->userHandler()->loadPoliciesByUserId($userId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::assignRole
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
     * @see eZ\Publish\SPI\Persistence\User\Handler::unAssignRole
     */
    public function unAssignRole($contentId, $roleId)
    {
        $this->logger->logCall(__METHOD__, array('group' => $contentId, 'role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->unAssignRole($contentId, $roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited');

        return $return;
    }
}
