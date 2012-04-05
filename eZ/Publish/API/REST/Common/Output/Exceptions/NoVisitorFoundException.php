<?php
/**
 * File containing the NoVisitorFoundException class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output\Exceptions;

/**
 * No putput visitor found exception
 */
class NoVisitorFoundException extends \RuntimeException
{
    /**
     * Construct from tested classes
     *
     * @param array $classes
     * @return void
     */
    public function __construct( array $classes )
    {
        parent::__construct(
            sprintf(
                "No freaking visitor found for %s!",
                implode( ', ', $classes )
            )
        );
    }
}
