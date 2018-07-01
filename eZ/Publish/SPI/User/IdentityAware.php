<?php

/**
 * File containing the IdentityAware interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\User;

/**
 * Interface for "user identity-aware" services.
 */
interface IdentityAware
{
    public function setIdentity(Identity $identity);
}
