<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to copy an existing role.
 */
abstract class RoleCopyStruct extends ValueObject
{
    /**
     * Readable string identifier of a new role.
     *
     * @var string
     */
    public $newIdentifier;

    /**
     * Returns policies associated with the role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    abstract public function getPolicies(): array;

    /**
     * Adds a policy to this role.
     */
    abstract public function addPolicy(PolicyCreateStruct $policyCreateStruct);
}
