<?php
/**
 * Contains Invalid Argument BadContentType Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    InvalidArgumentException;

/**
 * Invalid Argument BadContentType Exception implementation
 *
 * @use: throw new BadContentType( 'User Group', 'Article' );
 *
 */
class BadContentType extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Expected Content Type '{$excepted}' but got '{$got}' instead
     *
     * @param string $excepted
     * @param string $got
     * @param \Exception|null $previous
     */
    public function __construct( $excepted, $got, PHPException $previous = null )
    {
        parent::__construct( "Expected Content Type '{$excepted}' but got '{$got}' instead", 0, $previous );
    }
}
