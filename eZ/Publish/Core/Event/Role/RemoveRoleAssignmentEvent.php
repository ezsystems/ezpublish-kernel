<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\Core\Event\AfterEvent;

final class RemoveRoleAssignmentEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleAssignment
     */
    private $roleAssignment;

    public function __construct(
        RoleAssignment $roleAssignment
    ) {
        $this->roleAssignment = $roleAssignment;
    }

    public function getRoleAssignment(): RoleAssignment
    {
        return $this->roleAssignment;
    }
}
