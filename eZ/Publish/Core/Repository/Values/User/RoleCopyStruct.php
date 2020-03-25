<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleCopyStruct as APIRoleCopyStruct;

/**
 * This class is used to create a new role.
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class RoleCopyStruct extends APIRoleCopyStruct
{
    /**
     * Policies associated with the role.
     *
     * @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    protected $policies = [];

    /**
     * Returns policies associated with the role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Adds a policy to this role.
     */
    public function addPolicy(APIPolicyCreateStruct $policyCopy)
    {
        $this->policies[] = $policyCopy;
    }
}
