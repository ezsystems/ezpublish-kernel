<?php
/**
 * Contains Illegal Argument Type Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;
use eZ\Publish\API\Repository\Exceptions\IllegalArgumentException as APIIllegalArgumentException,
    Exception;

/**
 * Illegal Argument Type Exception implementation
 *
 * @use: throw new IllegalArgument( 'nodes', 'array' );
 *
 */
class IllegalArgumentException extends APIIllegalArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is illegal: {$whatIsWrong}"
     *
     * @param string $argumentName
     * @param string $whatIsWrong
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $whatIsWrong, Exception $previous = null )
    {
        parent::__construct(
            "Argument '{$argumentName}' is illegal: {$whatIsWrong}",
            0,
            $previous
        );
    }

    /**
     * Returns an additional error code which indicates why an action could not be performed
     * @todo implement
     *
     * @return int An error code
     */
    public function getErrorCode()
    {
        return 0;
    }
}
