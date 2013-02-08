<?php
/**
 * Contains Invalid Argument Type Exception implementation
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException as APIInvalidArgumentException;
use Exception;

/**
 * Invalid Argument Type Exception implementation
 *
 * @use: throw new InvalidArgumentException( 'nodes', 'array' );
 */
class InvalidArgumentException extends APIInvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: {$whatIsWrong}"
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $whatIsWrong, Exception $previous = null )
    {
        parent::__construct(
            "Argument '{$argumentName}' is invalid: {$whatIsWrong}",
            0,
            $previous
        );
    }
}
