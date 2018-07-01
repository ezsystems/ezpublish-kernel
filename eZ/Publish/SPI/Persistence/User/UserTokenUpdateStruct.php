<?php

/**
 * File containing the UserTokenUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This update struct is used to update User Tokens (formerly known as User account keys).
 */
class UserTokenUpdateStruct extends ValueObject
{
    /**
     * Hash key date for user account.
     *
     * @var string
     */
    public $hashKey;

    /**
     * Time to which the token is valid
     * Unix timestamp.
     *
     * @var int
     */
    public $time;

    /**
     * The user to whom the token belongs.
     *
     * @var int
     */
    public $userId;
}
