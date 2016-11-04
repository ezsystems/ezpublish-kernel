<?php

/**
 * File containing the CreatedPolicy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created policy.
 */
class CreatedPolicy extends ValueObject
{
    /**
     * The created policy.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Policy
     */
    public $policy;
}
