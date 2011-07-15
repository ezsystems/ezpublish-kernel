<?php
/**
 * Contains Forbidden Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;

/**
 * Forbidden Exception implementation
 *
 * Use:
 *   throw new Forbidden( 'Content', 'create' );
 *
 */
class Forbidden extends AbstractHttp
{
    /**
     * Generates: User does not have access to $action '{$type}'
     *
     * @param string $type
     * @param string $action
     * @param \Exception|null $previous
     */
    public function __construct( $type, $action, \Exception $previous = null )
    {
        parent::__construct( "User did not have access to {$action} '{$type}'", self::FORBIDDEN, $previous );
    }
}
