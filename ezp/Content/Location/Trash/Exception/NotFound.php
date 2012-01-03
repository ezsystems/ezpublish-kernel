<?php
/**
 * Contains NotFound Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location\Trash\Exception;
use Exception as PHPException,
    ezp\Base\Exception\NotFound as BaseNotFound;

/**
 * NotFound exception implementation
 */
class NotFound extends BaseNotFound
{
    /**
     * Constructor
     *
     * @param integer $trashedLocationId Location Id that cannot be found
     * @param PHPException $previous
     */
    public function __construct( $trashedLocationId, PHPException $previous = null )
    {
        parent::__construct( "Trashed Location", $trashedLocationId, $previous );
    }
}
