<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\RoleCopyStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeCopyRoleEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleCopyStruct */
    private $roleCopyStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\Role|null */
    private $copiedRole;

    public function __construct(Role $role, RoleCopyStruct $roleCopyStruct)
    {
        $this->role = $role;
        $this->roleCopyStruct = $roleCopyStruct;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function getCopiedRole(): Role
    {
        if (!$this->hasCopiedRole()) {
            throw new BadStateException(self::class, 'Event does not posses CopiedRole object.');
        }

        return $this->copiedRole;
    }

    public function hasCopiedRole(): bool
    {
        return $this->copiedRole instanceof Role;
    }
}
