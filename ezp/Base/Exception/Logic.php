<?php
/**
 * Contains BadConfiguration Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    LogicException;

/**
 * BadConfiguration Exception implementation
 *
 * Use:
 *   throw new BadConfiguration( "base\\[configuration]\\parsers", "could not parse configuration files" );
 *
 */
class Logic extends LogicException implements Exception
{
    /**
     * Generates: '$what' has a logic error[, $consequence]
     *
     * @param string $what
     * @param string|null $consequence Optional string to explain consequence of configuration mistake
     * @param PHPException|null $previous
     */
    public function __construct( $what, $consequence = null, PHPException $previous = null )
    {
        if ( $consequence === null )
            parent::__construct( "'{$what}' has a logic error", 0, $previous );
        else
            parent::__construct( "'{$what}' has a logic error, {$consequence}", 0, $previous );
    }
}
