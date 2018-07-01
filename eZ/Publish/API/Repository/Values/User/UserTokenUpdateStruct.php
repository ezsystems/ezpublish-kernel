<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a user token in the repository.
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
     * Time to which the token is valid.
     *
     * @var \DateTime|null
     */
    public $time;
}
