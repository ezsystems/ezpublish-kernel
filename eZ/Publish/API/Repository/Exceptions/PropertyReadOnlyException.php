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
 * This Exception is thrown on a write attempt in a read only property in a value object.
 */
class PropertyReadOnlyException extends Exception implements RepositoryException
{
    /**
     * Generates: Property '{$propertyName}' is readonly[ on class '{$className}'].
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct($propertyName, $className = null, Exception $previous = null)
    {
        if ($className === null) {
            parent::__construct("Property '{$propertyName}' is readonly", 0, $previous);
        } else {
            parent::__construct("Property '{$propertyName}' is readonly on class '{$className}'", 0, $previous);
        }
    }
}
