<?php
/**
 * Contains NotFound Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location\Exception;
use Exception as PHPException,
    ezp\Base\Exception\NotFound as BaseNotFound;

/**
 * NotFound exception implementation
 *
 * Use:
 * <code>
 * // Assuming we can't find location #62
 * throw new NotFound( 62 );
 *
 * // Other example with Location domain object :
 * throw new NotFound( $location->id );
 * </code>
 */
class NotFound extends BaseNotFound
{
    /**
     * Constructor
     *
     * @param integer $locationId Location Id that cannot be found
     * @param PHPException $previous
     */
    public function __construct( $locationId, PHPException $previous = null )
    {
        parent::__construct( "Location", $locationId, $previous );
    }
}
