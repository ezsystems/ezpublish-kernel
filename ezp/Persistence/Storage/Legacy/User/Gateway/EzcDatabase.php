<?php
/**
 * File containing the EzcDatabase location gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\User\Gateway;
use ezp\Persistence\Storage\Legacy\User\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\User;

/**
 * User gateway implementation using the zeta database component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param EzcDbHandler $handler
     * @return void
     */
    public function __construct( EzcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Create user
     *
     * @param user $user
     * @return mixed
     */
    public function createUser( User $user )
    {
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( 'ezuser' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $user->id )
            )->set(
                $this->handler->quoteColumn( 'login' ),
                $query->bindValue( $user->login )
            )->set(
                $this->handler->quoteColumn( 'email' ),
                $query->bindValue( $user->email )
            )->set(
                $this->handler->quoteColumn( 'password_hash' ),
                $query->bindValue( $user->password )
            )->set(
                $this->handler->quoteColumn( 'password_hash_type' ),
                $query->bindValue( $user->hashAlgorithm )
            );
        $query->prepare()->execute();
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function deleteUser( $userId )
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezuser' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $userId )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    public function updateUser( User $user )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezuser' ) )
            ->set(
                $this->handler->quoteColumn( 'login' ),
                $query->bindValue( $user->login )
            )->set(
                $this->handler->quoteColumn( 'email' ),
                $query->bindValue( $user->email )
            )->set(
                $this->handler->quoteColumn( 'password_hash' ),
                $query->bindValue( $user->password )
            )->set(
                $this->handler->quoteColumn( 'password_hash_type' ),
                $query->bindValue( $user->hashAlgorithm )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $user->id )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return UserPolicy[]
     */
    public function loadPoliciesByUserId( $userId )
    {

    }

    /**
     * Assign role to user with given limitation
     *
     * @param mixed $userId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $userId, $roleId, array $limitation )
    {
        foreach ( $limitation as $identifier => $values )
        {
            foreach ( $values as $value )
            {
                $query = $this->handler->createInsertQuery();
                $query
                    ->insertInto( $this->handler->quoteTable( 'ezuser_role' ) )
                    ->set(
                        $this->handler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $userId )
                    )->set(
                        $this->handler->quoteColumn( 'role_id' ),
                        $query->bindValue( $roleId )
                    )->set(
                        $this->handler->quoteColumn( 'limit_identifier' ),
                        $query->bindValue( $identifier )
                    )->set(
                        $this->handler->quoteColumn( 'limit_value' ),
                        $query->bindValue( $value )
                    );
                $query->prepare()->execute();
            }
        }
    }

    /**
     * Remove role from user
     *
     * @param mixed $userId
     * @param mixed $roleId
     */
    public function removeRole( $userId, $roleId )
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezuser_role' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $userId )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'role_id' ),
                        $query->bindValue( $roleId )
                    )
                )
            );
        $query->prepare()->execute();
    }
}
