<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

/**
 * @property-read mixed $originalId Original policy ID the policy was created from.
 */
abstract class PolicyDraft extends Policy
{
    /**
     * Original policy ID the policy was created from.
     * Used when role status is Role::STATUS_DRAFT.
     *
     * @var int
     */
    protected $originalId;
}
