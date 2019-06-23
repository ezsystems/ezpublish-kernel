<?php

/**
 * File containing the ParameterNotFoundException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Exception;

use InvalidArgumentException;

/**
 * This exception is thrown when a dynamic parameter could not be found in any scope.
 */
class ParameterNotFoundException extends InvalidArgumentException
{
    public function __construct($paramName, $namespace, array $triedScopes = [])
    {
        $this->message = "Parameter '$paramName' with namespace '$namespace' could not be found.";
        if (!empty($triedScopes)) {
            $this->message .= ' Tried scopes: ' . implode(', ', $triedScopes);
        }
    }
}
