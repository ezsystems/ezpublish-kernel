<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Exception;

use Exception;

/**
 * Invalid Argument Type Exception implementation.
 *
 * Usage: throw new InvalidArgument( 'nodes', 'array' );
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
    public function __construct(string $argumentName, string $expectedType, $value = null, Exception $previous = null)
    {
        if ($value !== null) {
            $actualType = is_object($value) ? get_class($value) : gettype($value);
            $whatIsWrong = sprintf("Received '%s' instead of expected value of type '%s'", $actualType, $expectedType);
        } else {
            $whatIsWrong = sprintf("Expected value is of type '%s'", $expectedType);
        }

        parent::__construct($argumentName, $whatIsWrong, $previous);
    }
}
