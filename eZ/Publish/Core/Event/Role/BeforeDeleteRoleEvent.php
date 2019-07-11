<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleEvent as BeforeDeleteRoleEventInterface;
use eZ\Publish\API\Repository\Values\User\Role;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeDeleteRoleEvent extends Event implements BeforeDeleteRoleEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
