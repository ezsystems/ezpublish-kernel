<?php
/**
 * File containing a User Handler impl
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * Cache handler for user module
 */
class UserHandler extends AbstractHandler implements UserHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::create
     */
    public function create( User $user )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $user ) );
        return $this->persistenceFactory->getUserHandler()->create( $user );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::load
     */
    public function load( $userId )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $userId ) );
        return $this->persistenceFactory->getUserHandler()->load( $userId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByLogin
     */
    public function loadByLogin( $login, $alsoMatchEmail = false )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $login, 'email?' => $alsoMatchEmail ) );
        return $this->persistenceFactory->getUserHandler()->loadByLogin( $login, $alsoMatchEmail );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::update
     */
    public function update( User $user )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $user ) );
        return $this->persistenceFactory->getUserHandler()->update( $user );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::delete
     */
    public function delete( $userId )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $userId ) );
        $return = $this->persistenceFactory->getUserHandler()->delete( $userId );

        // user id == content id == group id
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', $userId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', 'inherited', $userId );

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::createRole
     */
    public function createRole( Role $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $struct ) );
        $role = $this->persistenceFactory->getUserHandler()->createRole( $struct );

        $this->cache->getItem( 'user', 'role', $role->id )->set( $role );

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRole
     */
    public function loadRole( $roleId )
    {
        $cache = $this->cache->getItem( 'user', 'role', $roleId );
        $role = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'role' => $roleId ) );
            $role = $this->persistenceFactory->getUserHandler()->loadRole( $roleId );
            $cache->set( $role );
        }

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleByIdentifier
     */
    public function loadRoleByIdentifier( $identifier )
    {
        $this->logger->logCall( __METHOD__, array( 'role' => $identifier ) );
        return $this->persistenceFactory->getUserHandler()->loadRoleByIdentifier( $identifier );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoles
     */
    public function loadRoles()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getUserHandler()->loadRoles();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByRoleId
     */
    public function loadRoleAssignmentsByRoleId( $roleId )
    {
        $this->logger->logCall( __METHOD__, array( 'role' => $roleId ) );
        return $this->persistenceFactory->getUserHandler()->loadRoleAssignmentsByRoleId( $roleId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByGroupId
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherit = false )
    {
        $cacheKey = ( $inherit ? 'inherited/' : '' ) . $groupId;
        $cache = $this->cache->getItem( 'user', 'role', 'assignments', 'byGroup', $cacheKey );
        $assignments = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'group' => $groupId, 'inherit' => $inherit ) );
            $assignments = $this->persistenceFactory->getUserHandler()->loadRoleAssignmentsByGroupId(
                $groupId,
                $inherit
            );
            $cache->set( $assignments );
        }

        return $assignments;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updateRole
     */
    public function updateRole( RoleUpdateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $struct ) );
        $role = $this->persistenceFactory->getUserHandler()->updateRole( $struct );

        $this->cache->getItem( 'user', 'role', $role->id )->set( $role );

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deleteRole
     */
    public function deleteRole( $roleId )
    {
        $this->logger->logCall( __METHOD__, array( 'role' => $roleId ) );
        $return = $this->persistenceFactory->getUserHandler()->deleteRole( $roleId );

        $this->cache->clear( 'user', 'role', $roleId );
        $this->cache->clear( 'user', 'role', 'assignments' );

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicy
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $this->logger->logCall( __METHOD__, array( 'role' => $roleId, 'struct' => $policy ) );
        $return = $this->persistenceFactory->getUserHandler()->addPolicy( $roleId, $policy );

        $this->cache->clear( 'user', 'role', $roleId );

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updatePolicy
     */
    public function updatePolicy( Policy $policy )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $policy ) );
        $return = $this->persistenceFactory->getUserHandler()->updatePolicy( $policy );

        $this->cache->clear( 'user', 'role', $policy->roleId );

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::removePolicy
     */
    public function removePolicy( $roleId, $policyId )
    {
        $this->logger->logCall( __METHOD__, array( 'role' => $roleId, 'policy' => $policyId ) );
        $this->persistenceFactory->getUserHandler()->removePolicy( $roleId, $policyId );

        $this->cache->clear( 'user', 'role', $roleId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadPoliciesByUserId
     */
    public function loadPoliciesByUserId( $userId )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $userId ) );
        return $this->persistenceFactory->getUserHandler()->loadPoliciesByUserId( $userId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::assignRole
     */
    public function assignRole( $contentId, $roleId, array $limitation = null )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $contentId, 'role' => $roleId, 'limitation' => $limitation ) );
        $return = $this->persistenceFactory->getUserHandler()->assignRole( $contentId, $roleId, $limitation );

        $this->cache->clear( 'user', 'role', $roleId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', $contentId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', 'inherited' );

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::unAssignRole
     */
    public function unAssignRole( $contentId, $roleId )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $contentId, 'role' => $roleId ) );
        $return = $this->persistenceFactory->getUserHandler()->unAssignRole( $contentId, $roleId );

        $this->cache->clear( 'user', 'role', $roleId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', $contentId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup', 'inherited' );

        return $return;
    }
}
