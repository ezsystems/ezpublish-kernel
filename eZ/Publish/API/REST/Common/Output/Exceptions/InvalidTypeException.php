<?php
/**
 * File containing the InvalidTypeException class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output\Exceptions;

/**
 * Output visiting invalid type exception
 */
class InvalidTypeException extends \RuntimeException
{
    /**
     * Construct from invalid data
     *
     * @param mixed $data
     * @return void
     */
    public function __construct( $data )
    {
        parent::__construct(
            'You must provide a ValueObject for visiting, "' . gettype( $data ) . '" provided.'
        );
    }
}
