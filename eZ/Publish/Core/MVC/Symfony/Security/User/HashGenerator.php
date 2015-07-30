<?php

/**
 * File containing the user HashGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\SPI\HashGenerator as HashGeneratorInterface;
use eZ\Publish\SPI\User\Identity as IdentityInterface;
use eZ\Publish\SPI\User\IdentityAware;
use FOS\HttpCache\UserContext\ContextProviderInterface;
use FOS\HttpCache\UserContext\UserContext;

/**
 * User hash generator.
 *
 * @deprecated since 5.4. Will be removed in 6.0. Use FOSHttpCacheBundle user context feature instead.
 */
class HashGenerator implements HashGeneratorInterface, IdentityAware, ContextProviderInterface
{
    /**
     * @var IdentityInterface
     */
    protected $userIdentity;

    /**
     * @var IdentityAware[]
     */
    protected $identityDefiners = array();

    /**
     * @param IdentityAware $identityDefiner
     */
    public function setIdentityDefiner(IdentityAware $identityDefiner)
    {
        $this->identityDefiners[] = $identityDefiner;
    }

    /**
     * @return IdentityAware[]
     */
    public function getIdentityDefiners()
    {
        return $this->identityDefiners;
    }

    /**
     * @param IdentityInterface $identity
     */
    public function setIdentity(IdentityInterface $identity)
    {
        $this->userIdentity = $identity;
    }

    /**
     * @return IdentityInterface
     */
    public function getIdentity()
    {
        return $this->userIdentity;
    }

    /**
     * Generates the user hash.
     *
     * @return string
     */
    public function generate()
    {
        foreach ($this->getIdentityDefiners() as $identityDefiner) {
            $identityDefiner->setIdentity($this->userIdentity);
        }

        return $this->userIdentity->getHash();
    }

    public function updateUserContext(UserContext $context)
    {
        $context->addParameter('ezpublish_identity', $this->generate());
    }
}
