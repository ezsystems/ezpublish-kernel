<?php

/**
 * File containing the StorageNotFound class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use InvalidArgumentException;

/**
 * Exception thrown no storage for a type was found.
 */
class StorageNotFound extends InvalidArgumentException
{
    /**
     * Creates a new exception for $typeName.
     *
     * @param mixed $typeName
     */
    public function __construct($typeName)
    {
        parent::__construct(
            sprintf('Storage for type "%s" not found.', $typeName)
        );
    }
}
