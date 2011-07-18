<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\User\UserGateway;
use ezp\Persistence\LegacyStorage\User\UserGateway,
    ezp\Persistence\User,
    ezp\Persistence\User\Role;

/**
 * Base class for content type gateways.
 */
class EzcDatabase extends UserGateway
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
            ->set( 'contentobject_id',   $query->bindvalue( $user->id ) )
            ->set( 'login',              $query->bindvalue( $user->login ) )
            ->set( 'email',              $query->bindvalue( '' ) )
            ->set( 'password_hash',      $query->bindvalue( $user->pwd ) )
            ->set( 'password_hash_type', $query->bindvalue( $user->hashAlg ) );
        $query->prepare()->execute();
    }
}
