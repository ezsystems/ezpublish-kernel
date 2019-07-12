<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Exception;

use Exception;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException as APIInvalidArgumentException;

/**
 * Invalid Argument Type Exception implementation.
 *
 * Usage: throw new InvalidArgumentException( 'nodes', 'array' );
 */
class InvalidArgumentException extends APIInvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: {$whatIsWrong}".
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct(string $argumentName, string $whatIsWrong, Exception $previous = null)
    {
        $message = sprintf("Argument '%s' is invalid: %s", $argumentName, $whatIsWrong);

        parent::__construct($message, 0, $previous);
    }
}
