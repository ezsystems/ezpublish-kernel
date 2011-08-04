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
    ezp\Persistence\User;

/**
 * User gateway implementation using the zeta database component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
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
            ->insertInto( 'ezuser' )
            ->set( 'contentobject_id', $query->bindValue( $user->id ) )
            ->set( 'login', $query->bindValue( $user->login ) )
            ->set( 'email', $query->bindValue( $user->email ) )
            ->set( 'password_hash', $query->bindValue( $user->password ) )
            ->set( 'password_hash_type', $query->bindValue( $user->hashAlgorithm ) );
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
            ->deleteFrom( 'ezuser' )
            ->where( $query->expr->eq( 'contentobject_id', $query->bindValue( $userId ) ) );
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
            ->update( 'ezuser' )
            ->set( 'login', $query->bindValue( $user->login ) )
            ->set( 'email', $query->bindValue( $user->email ) )
            ->set( 'password_hash', $query->bindValue( $user->password ) )
            ->set( 'password_hash_type', $query->bindValue( $user->hashAlgorithm ) )
            ->where( $query->expr->eq( 'contentobject_id', $query->bindValue( $user->id ) ) );
        $query->prepare()->execute();
    }
}
