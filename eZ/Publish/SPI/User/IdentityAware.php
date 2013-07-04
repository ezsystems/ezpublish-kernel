<?php
/**
 * File containing the IdentityAware interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\User;

/**
 * Interface for "user identity-aware" services.
 */
interface IdentityAware
{
    public function setIdentity( Identity $identity );
}
