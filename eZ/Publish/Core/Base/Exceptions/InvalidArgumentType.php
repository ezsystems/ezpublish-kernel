<?php

/**
 * Contains Invalid Argument Type Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

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
    public function __construct($argumentName, $expectedType, $value = null, Exception $previous = null)
    {
        $parameters = ['%argumentName%' => $argumentName, '%expectedType%' => $expectedType];
        $this->setMessageTemplate("Argument '%argumentName%' is invalid: expected value to be of type '%expectedType%'");
        if ($value) {
            $this->setMessageTemplate("Argument '%argumentName%' is invalid: expected value to be of type '%expectedType%', got '%actualType%'");
            $actualType = is_object($value) ? get_class($value) : gettype($value);
            $parameters['%actualType%'] = $actualType;
        }

        /** @Ignore */
        $this->addParameters($parameters);
        $this->message = $this->getBaseTranslation();
    }
}
