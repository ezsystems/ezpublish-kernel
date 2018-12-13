<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Exceptions;

use Exception;
use eZ\Publish\Core\FieldType\ValidationError;

class UserPasswordValidationException extends InvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: Password doesn't match the following rules: {X}, {Y}, {Z}".
     *
     * @param string $argumentName
     * @param array $errors
     * @param Exception|null $previous
     */
    public function __construct(string $argumentName, array $errors, Exception $previous = null)
    {
        $rules = array_map(function (ValidationError $error) {
            return (string) $error->getTranslatableMessage();
        }, $errors);

        parent::__construct($argumentName, "Password doesn't match the following rules: " . implode(', ', $rules), $previous);
    }
}
