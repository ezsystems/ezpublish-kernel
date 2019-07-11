<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeUpdatePolicyEvent as BeforeUpdatePolicyEventInterface;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeUpdatePolicyEvent extends Event implements BeforeUpdatePolicyEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\Policy */
    private $policy;

    /** @var \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct */
    private $policyUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\Policy|null */
    private $updatedPolicy;

    public function __construct(Policy $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $this->policy = $policy;
        $this->policyUpdateStruct = $policyUpdateStruct;
    }

    public function getPolicy(): Policy
    {
        return $this->policy;
    }

    public function getPolicyUpdateStruct(): PolicyUpdateStruct
    {
        return $this->policyUpdateStruct;
    }

    public function getUpdatedPolicy(): Policy
    {
        if (!$this->hasUpdatedPolicy()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedPolicy() or set it by setUpdatedPolicy() before you call getter.', Policy::class));
        }

        return $this->updatedPolicy;
    }

    public function setUpdatedPolicy(?Policy $updatedPolicy): void
    {
        $this->updatedPolicy = $updatedPolicy;
    }

    public function hasUpdatedPolicy(): bool
    {
        return $this->updatedPolicy instanceof Policy;
    }
}
