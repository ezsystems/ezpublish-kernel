<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Exceptions;

use eZ\Publish\API\Repository\Exceptions\Exception as RepositoryException;
use Exception;

/**
 * This Exception is thrown if an accessed property in a value object was not found.
 */
class PropertyNotFoundException extends Exception implements RepositoryException
{
    /**
     * Generates: Property '{$propertyName}' not found.
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct($propertyName, $className = null, Exception $previous = null)
    {
        if ($className === null) {
            parent::__construct("Property '{$propertyName}' not found", 0, $previous);
        } else {
            parent::__construct("Property '{$propertyName}' not found on class '{$className}'", 0, $previous);
        }
    }
}
