<?php
/**
 * File containing the User Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User\Gateway;

use eZ\Publish\Core\Persistence\Legacy\User\Gateway;
use eZ\Publish\SPI\Persistence\User;
use ezcDbException;
use PDOException;
use RuntimeException;

/**
 * Base class for user gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Create user
     *
     * @param user $user
     *
     * @return mixed
     */
    public function createUser( User $user )
    {
        try
        {
            return $this->innerGateway->createUser( $user );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function deleteUser( $userId )
    {
        try
        {
            return $this->innerGateway->deleteUser( $userId );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return array
     */
    public function load( $userId )
    {
        try
        {
            return $this->innerGateway->load( $userId );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @return array
     */
    public function loadByLogin( $login )
    {
        try
        {
            return $this->innerGateway->loadByLogin( $login );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads user with user email.
     *
     * @param string $email
     *
     * @return array
     */
     public function loadByEmail( $email )
     {
         try
         {
             return $this->innerGateway->loadByEmail( $email );
         }
         catch ( \ezcDbException $e )
         {
             throw new \RuntimeException( 'Database error', 0, $e );
         }
         catch ( \PDOException $e )
         {
             throw new \RuntimeException( 'Database error', 0, $e );
         }
     }

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    public function updateUser( User $user )
    {
        try
        {
            return $this->innerGateway->updateUser( $user );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Assigns role to user with given limitation
     *
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $contentId, $roleId, array $limitation )
    {
        try
        {
            return $this->innerGateway->assignRole( $contentId, $roleId, $limitation );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Remove role from user
     *
     * @param mixed $contentId
     * @param mixed $roleId
     */
    public function removeRole( $contentId, $roleId )
    {
        try
        {
            return $this->innerGateway->removeRole( $contentId, $roleId );
        }
        catch ( ezcDbException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }
}
