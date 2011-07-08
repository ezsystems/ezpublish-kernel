<?php
/**
 * Contains Not Found Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\base\Exception;

/**
 * Not Found Exception implementation
 *
 * Use:
 *   throw new NotFound( 'Content', 42 );
 *
 * @package ezp
 * @subpackage base
 */
class NotFound extends AbstractHttp
{
    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'
     *
     * @param string $what
     * @param string|int $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $what, $identifier, \Exception $previous = null )
    {
        parent::__construct( "Could not find '{$what}' with identifier '{$identifier}'", self::NOT_FOUND, $previous );
    }
}