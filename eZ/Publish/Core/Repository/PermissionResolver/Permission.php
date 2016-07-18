<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\PermissionResolver;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * todo
 *
 * @property-read null|\eZ\Publish\API\Repository\Values\User\Limitation $limitation
 * @property-read \eZ\Publish\API\Repository\Values\User\Policy[] $policies
 */
class Permission extends ValueObject
{
    /**
     * @var null|\eZ\Publish\API\Repository\Values\User\Limitation
     */
    protected $limitation;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    protected $policies = [];
}
