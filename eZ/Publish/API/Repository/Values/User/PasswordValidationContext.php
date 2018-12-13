<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Context of the password validation.
 *
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType
 * @property-read \eZ\Publish\API\Repository\Values\User\User|null $user
 */
class PasswordValidationContext extends ValueObject
{
    /**
     * Content type of the password owner.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType|null
     */
    protected $contentType;

    /**
     * Owner of the password.
     *
     * @var \eZ\Publish\API\Repository\Values\User\User|null
     */
    protected $user;
}
