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
        $this->logger->startLogCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->create($user);

        // Clear corresponding content cache as creation of the User changes it's external data
        $this->cache->clear('content', $user->id);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::load
     */
    public function load($userId)
    {
        $this->logger->startLogCall(__METHOD__, array('user' => $userId));

        $return = $this->persistenceHandler->userHandler()->load($userId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByLogin
     */
    public function loadByLogin($login)
    {
        $this->logger->startLogCall(__METHOD__, array('user' => $login));

        $return = $this->persistenceHandler->userHandler()->loadByLogin($login);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByEmail
     */
    public function loadByEmail($email)
    {
        $this->logger->startLogCall(__METHOD__, array('email' => $email));

        $return = $this->persistenceHandler->userHandler()->loadByEmail($email);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::update
     */
    public function update(User $user)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->update($user);

        // Clear corresponding content cache as update of the User changes it's external data
        $this->cache->clear('content', $user->id);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::delete
     */
    public function delete($userId)
    {
        $this->logger->startLogCall(__METHOD__, array('user' => $userId));
        $return = $this->persistenceHandler->userHandler()->delete($userId);

        // user id == content id == group id
        $this->cache->clear('content', $userId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $userId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited', $userId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::createRole
     */
    public function createRole(Role $struct)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $struct));
        $role = $this->persistenceHandler->userHandler()->createRole($struct);

        $this->cache->getItem('user', 'role', $role->id)->set($role);

        $this->logger->stopLogCall(__METHOD__);

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRole
     */
    public function loadRole($roleId)
    {
        $cache = $this->cache->getItem('user', 'role', $roleId);
        $role = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->startLogCall(__METHOD__, array('role' => $roleId));
            $role = $this->persistenceHandler->userHandler()->loadRole($roleId);
            $cache->set($role);
            $this->logger->stopLogCall(__METHOD__);
        }

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleByIdentifier
     */
    public function loadRoleByIdentifier($identifier)
    {
        $this->logger->startLogCall(__METHOD__, array('role' => $identifier));

        $return = $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoles
     */
    public function loadRoles()
    {
        $this->logger->startLogCall(__METHOD__);

        $return = $this->persistenceHandler->userHandler()->loadRoles();

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByRoleId
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        $this->logger->startLogCall(__METHOD__, array('role' => $roleId));

        $return = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
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
            $this->logger->startLogCall(__METHOD__, array('group' => $groupId, 'inherit' => $inherit));
            $assignments = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId(
                $groupId,
                $inherit
            );
            $cache->set($assignments);
            $this->logger->stopLogCall(__METHOD__);
        }

        return $assignments;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updateRole
     */
    public function updateRole(RoleUpdateStruct $struct)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $struct));
        $this->persistenceHandler->userHandler()->updateRole($struct);

        $this->cache->clear('user', 'role', $struct->id);
        $this->logger->stopLogCall(__METHOD__);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deleteRole
     */
    public function deleteRole($roleId)
    {
        $this->logger->startLogCall(__METHOD__, array('role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments');
        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $this->logger->startLogCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->addPolicy($roleId, $policy);

        $this->cache->clear('user', 'role', $roleId);
        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updatePolicy
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->startLogCall(__METHOD__, array('struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->clear('user', 'role', $policy->roleId);
        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deletePolicy
     */
    public function deletePolicy($policyId)
    {
        $this->logger->startLogCall(__METHOD__, array('policy' => $policyId));
        $this->persistenceHandler->userHandler()->deletePolicy($policyId);

        $this->cache->clear('user', 'role');
        $this->logger->stopLogCall(__METHOD__);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadPoliciesByUserId
     */
    public function loadPoliciesByUserId($userId)
    {
        $this->logger->startLogCall(__METHOD__, array('user' => $userId));

        $return = $this->persistenceHandler->userHandler()->loadPoliciesByUserId($userId);

        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::assignRole
     */
    public function assignRole($contentId, $roleId, array $limitation = null)
    {
        $this->logger->startLogCall(__METHOD__, array('group' => $contentId, 'role' => $roleId, 'limitation' => $limitation));
        $return = $this->persistenceHandler->userHandler()->assignRole($contentId, $roleId, $limitation);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited');
        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::unAssignRole
     */
    public function unAssignRole($contentId, $roleId)
    {
        $this->logger->startLogCall(__METHOD__, array('group' => $contentId, 'role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->unAssignRole($contentId, $roleId);

        $this->cache->clear('user', 'role', $roleId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', $contentId);
        $this->cache->clear('user', 'role', 'assignments', 'byGroup', 'inherited');
        $this->logger->stopLogCall(__METHOD__);

        return $return;
    }
}
