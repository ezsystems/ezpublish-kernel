<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class is used to copy an existing role.
 */
abstract class RoleCopyStruct extends RoleCreateStruct
{
    /**
     * Readable string identifier of a new role.
     *
     * @var string
     */
    public $newIdentifier;

    /**
     * Status of a new role.
     *
     * @var int
     */
    public $status;
}
