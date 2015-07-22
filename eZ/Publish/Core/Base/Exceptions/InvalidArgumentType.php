<?php

/**
 * Contains Invalid Argument Type Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use Exception;

/**
 * Invalid Argument Type Exception implementation.
 *
 * @use: throw new InvalidArgument( 'nodes', 'array' );
 */
class InvalidArgumentType extends InvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: expected value to be of type '{$expectedType}'[, got '{$value}']".
     *
     * @param string $argumentName
     * @param string $expectedType
     * @param mixed|null $value Optionally to output the type that was received
     * @param \Exception|null $previous
     */
    public function __construct($argumentName, $expectedType, $value = null, Exception $previous = null)
    {
        if ($value === null) {
            $valueString = '';
        } elseif (is_object($value)) {
            $valueString = ", got '" . get_class($value) . "'";
        } else {
            $valueString = ", got '" . gettype($value) . "'";
        }

        parent::__construct(
            $argumentName,
            "expected value to be of type '{$expectedType}'" . $valueString,
            $previous
        );
    }
}
