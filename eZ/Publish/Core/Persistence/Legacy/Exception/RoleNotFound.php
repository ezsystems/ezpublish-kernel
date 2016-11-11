<?php

/**
 * File containing the RoleNotFound class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Exception thrown when a Role/RoleDraft to be loaded is not found.
 */
class RoleNotFound extends NotFoundException
{
    /**
     * Creates a new exception for $roleId in $status.
     *
     * @param mixed $roleId
     * @param mixed $status
     */
    public function __construct($roleId, $status)
    {
        parent::__construct(
            'eZ\\Publish\\SPI\\Persistence\\User\\Role',
            sprintf('ID: %s, Status: %s', $roleId, $status)
        );
    }
}
