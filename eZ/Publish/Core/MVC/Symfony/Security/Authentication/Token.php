<?php
/**
 * File containing the Token class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class Token extends AbstractToken
{
    /**
     * @param int $userId
     * @param \Symfony\Component\Security\Core\Role\Role[] $roles
     */
    public function __construct( $userId, array $roles = array() )
    {
        parent::__construct( $roles );
        $this->setAttribute( 'userId', $userId );
    }

    public function getUserId()
    {
        return $this->getAttribute( 'userId' );
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
