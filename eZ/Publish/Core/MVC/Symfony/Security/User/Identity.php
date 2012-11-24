<?php
/**
 * File containing the user Identity class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\User;

use eZ\Publish\SPI\User\Identity as IdentityInterface;

class Identity implements IdentityInterface
{
    /**
     * @var array
     */
    protected $identityInfo;

    /**
     * @var string
     */
    protected $hash;

    public function __construct()
    {
        $this->identityInfo = array();
    }

    /**
     * Registers several pieces of information in the identity.
     *
     * @param array $information Hash where key is the information type and value is a scalar.
     */
    public function addInformation( array $information )
    {
        $this->identityInfo += $information;
        $this->resetHash();
    }

    /**
     * Registers an information in the identity.
     *
     * @param $informationName
     * @param $informationValue
     */
    public function setInformation( $informationName, $informationValue )
    {
        $this->identityInfo[$informationName] = $informationValue;
        $this->resetHash();
    }

    /**
     * Replaces the information already registered in the identity.
     *
     * @param array $information Hash where key is the information type and value is a scalar.
     */
    public function replaceInformation( array $information )
    {
        $this->identityInfo = $information;
        $this->resetHash();
    }

    /**
     * Returns registered information.
     *
     * @return array
     */
    public function getInformation()
    {
        return $this->identityInfo;
    }

    /**
     * Resets current hash.
     */
    protected function resetHash()
    {
        $this->hash = null;
    }

    /**
     * Returns the hash of the current identity (e.g. md5, sha1...)
     *
     * @return string
     */
    public function getHash()
    {
        if ( !isset( $this->hash ) )
        {
            $hashArray = array();
            foreach ( $this->identityInfo as $infoType => $infoValue )
            {
                $hashArray[] = "$infoType=$infoValue";
            }

            $this->hash = sha1( implode( '-', $hashArray ) );
        }

        return $this->hash;
    }
}
