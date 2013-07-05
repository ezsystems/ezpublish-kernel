<?php
/**
 * File containing the user HashGenerator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\SPI\HashGenerator as HashGeneratorInterface;
use eZ\Publish\SPI\User\Identity;
use eZ\Publish\SPI\User\IdentityAware;

/**
 * User hash generator.
 */
class HashGenerator implements HashGeneratorInterface, IdentityAware
{
    /**
     * @var \eZ\Publish\SPI\User\Identity
     */
    protected $userIdentity;

    /**
     * @var IdentityAware[]
     */
    protected $identityDefiners = array();

    /**
     * @param \eZ\Publish\SPI\User\IdentityAware $identityDefiner
     */
    public function setIdentityDefiner( IdentityAware $identityDefiner )
    {
        $this->identityDefiners[] = $identityDefiner;
    }

    /**
     * @return \eZ\Publish\SPI\User\IdentityAware[]
     */
    public function getIdentityDefiners()
    {
        return $this->identityDefiners;
    }

    /**
     * @param Identity $identity
     */
    public function setIdentity( Identity $identity )
    {
        $this->userIdentity = $identity;
    }

    /**
     * @return Identity
     */
    public function getIdentity()
    {
        return $this->userIdentity;
    }

    /**
     * Generates the user hash
     *
     * @return string
     */
    public function generate()
    {
        foreach ( $this->getIdentityDefiners() as $identityDefiner )
        {
            $identityDefiner->setIdentity( $this->userIdentity );
        }

        return $this->userIdentity->getHash();
    }
}
