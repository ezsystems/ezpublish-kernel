<?php
/**
 * Contains ParentNotFound Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location\Exception;
use Exception as PHPException,
    ezp\Base\Exception\NotFound;

/**
 * ParentNotFound exception implementation
 *
 * Use:
 * <code>
 * // Assuming we can't find parent location for location #62
 * // This parent location originally had 2 as Id
 * throw new ParentNotFound( 62, 2 );
 *
 * // Other example with Location domain object :
 * throw new ParentNotFound( $location->id, $location->parentId );
 * </code>
 */
class ParentNotFound extends NotFound
{
    /**
     * Constructor
     *
     * @param integer $locationId Location Id which we can't find its parent
     * @param integer $parentLocationId Location Id we were originally looking for as $locationId's parent
     * @param PHPException $previous
     */
    public function __construct( $locationId, $parentLocationId, PHPException $previous = null )
    {
        parent::__construct( "Location #{$locationId}'s parent", $parentLocationId, $previous );
    }
}
