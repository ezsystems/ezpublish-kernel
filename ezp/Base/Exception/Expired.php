<?php
/**
 * Contains Expired Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * Expired Exception implementation
 *
 * Use:
 *   throw new Expired( 'Oauth Token' );
 *
 * @package ezp
 * @subpackage base
 */
class Expired extends AbstractHttp
{
    /**
     * Generates: '{$what}' has expired
     *
     * @param string $what
     * @param \Exception|null $previous
     */
    public function __construct( $what, \Exception $previous = null )
    {
        parent::__construct( "'{$what}' has expired", self::BAD_REQUEST, $previous );
    }
}
