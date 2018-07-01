<?php

/**
 * File containing the user Identity interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\User;

/**
 * Interface for a user identity.
 * One can add any kind of information that can then be hashed and used as a fingerprint.
 *
 * Typical use case is for content cache variation that you want to make vary on a bunch of user information (e.g. assigned roles).
 * The more you add information, the more specific and fine grained your cache variation will be.
 *
 * @deprecated since 5.4. Will be removed in 6.0. Use FOSHttpCacheBundle user context feature instead.
 */
interface Identity
{
    /**
     * Registers several pieces of information in the identity.
     *
     * @param array $information Hash where key is the information type and value is a scalar.
     */
    public function addInformation(array $information);

    /**
     * Registers an information in the identity.
     *
     * @param string $informationName
     * @param scalar $informationValue
     */
    public function setInformation($informationName, $informationValue);

    /**
     * Replaces the information already registered in the identity.
     *
     * @param array $information Hash where key is the information type and value is a scalar.
     */
    public function replaceInformation(array $information);

    /**
     * Returns registered information.
     *
     * @return array
     */
    public function getInformation();

    /**
     * Returns the hash of the current identity (e.g. md5, sha1...).
     *
     * @return string
     */
    public function getHash();
}
