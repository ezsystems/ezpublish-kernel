<?php
/**
 * File containing the Token class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken,
    eZ\Publish\API\Repository\Values\User\User as APIUser,
    eZ\Publish\Core\MVC\Symfony\Security\User;

class Token extends AbstractToken
{
    /**
     * @param string $module "module" the current user wants to access to (e.g. "content"). Terminology is the same than in 4.x permission system.
     * @param string $function "function" of the module (e.g. "read")
     * @param int|null $userId Current user ID. Can be null if anonymous.
     * @param \Symfony\Component\Security\Core\Role\Role[] $roles
     */
    public function __construct( $module, $function, $userId = null, array $roles = array() )
    {
        parent::__construct( $roles );
        $this->setAttributes(
            array(
                 'userId'   => $userId,
                 'module'   => $module,
                 'function' => $function
            )
        );

        if ( $userId )
            $this->setAuthenticated( true );
    }

    public function setUser( $user )
    {
        if ( !$user instanceof User )
            throw new \InvalidArgumentException( '$user must be an instance of eZ\\Publish\\API\\Repository\\Values\\User\\User' );

        parent::setUser( $user );
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}
