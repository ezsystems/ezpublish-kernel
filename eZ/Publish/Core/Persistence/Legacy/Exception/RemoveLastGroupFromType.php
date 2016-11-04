<?php

/**
 * File containing the RemoveLastGroupFromType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * Exception thrown when a Type is to be unlinked from its last Group.
 */
class RemoveLastGroupFromType extends BadStateException
{
    /**
     * Creates a new exception for $typeId in $status;.
     *
     * @param mixed $typeId
     * @param mixed $status
     */
    public function __construct($typeId, $status)
    {
        parent::__construct(
            '$typeId',
            sprintf(
                'Type with ID "%s" in status "%s" cannot be unlinked from its last group.',
                $typeId,
                $status
            )
        );
    }
}
