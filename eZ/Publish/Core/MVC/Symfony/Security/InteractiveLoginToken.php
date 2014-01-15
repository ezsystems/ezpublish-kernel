<?php
/**
 * File containing the InteractiveLoginToken class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This token is used when a user has been matched by a foreign user provider.
 * It is injected in SecurityContext to replace the original token as this one holds a new user.
 */
class InteractiveLoginToken extends UsernamePasswordToken
{
    /**
     * @var string
     */
    private $originalTokenType;

    public function __construct( UserInterface $user, $originalTokenType, $credentials, $providerKey, array $roles = array() )
    {
        parent::__construct( $user, $credentials, $providerKey, $roles );
        $this->originalTokenType = $originalTokenType;
    }

    /**
     * @return string
     */
    public function getOriginalTokenType()
    {
        return $this->originalTokenType;
    }

    public function serialize()
    {
        return serialize( array( $this->originalTokenType, parent::serialize() ) );
    }

    public function unserialize( $serialized )
    {
        list( $this->originalTokenType, $parentStr ) = unserialize( $serialized );
        parent::unserialize( $parentStr );
    }
}
