<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
     * @var mixed
     */
    protected $originalId;
}
