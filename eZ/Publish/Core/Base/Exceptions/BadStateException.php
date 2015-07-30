<?php

/**
 * Contains BadState Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException;
use Exception;

/**
 * BadState Exception implementation.
 *
 * @use: throw new BadState( 'nodes', 'array' );
 */
class BadStateException extends APIBadStateException
{
    /**
     * Generates: "Argument '{$argumentName}' has a bad state: {$whatIsWrong}".
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct($argumentName, $whatIsWrong, Exception $previous = null)
    {
        parent::__construct(
            "Argument '{$argumentName}' has a bad state: {$whatIsWrong}",
            0,
            $previous
        );
    }
}
