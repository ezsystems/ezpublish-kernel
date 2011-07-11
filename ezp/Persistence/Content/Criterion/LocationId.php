<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Location
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criterion
 */
class LocationId extends Criterion
{
    /**
     * Creates a new Location criterion
     *
     * @param integer|array(integer) $locationId One or more (as an array) location id
     */
    public function __construct( $locationId )
    {
        foreach( $locationId as $id )
        {
            if ( !is_numeric( $id ) )
            {
                throw new \InvalidArgumentException( "Only numeric location ids are accepted" );
            }
        }
        $this->locationIdList = $locationId;
    }

    /**
     */
    public $locationIdList;
}
?>
