<?php
/**
 * File containing the Location Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location;
use ezp\Persistence\Content\Location;

/**
 * Mapper for Location objects
 */
class Mapper
{
    /**
     * Creates a Location from a $data row
     *
     * $prefix can be used to define a table prefix for the location table
     *
     * @param array $rows
     * @param string $prefix
     * @return \ezp\Persistence\Content\Location
     */
    public function createLocationFromRow( array $data, $prefix = '' )
    {
        $location = new Location();

        $location->id = $data[$prefix . 'node_id'];
        $location->priority = $data[$prefix . 'priority'];
        $location->hidden = $data[$prefix . 'is_hidden'];
        $location->invisible = $data[$prefix . 'is_invisible'];
        $location->remoteId = $data[$prefix . 'remote_id'];
        $location->contentId = $data[$prefix . 'contentobject_id'];
        $location->parentId = $data[$prefix . 'parent_node_id'];
        $location->pathIdentificationString = $data[$prefix . 'path_identification_string'];
        $location->pathString = $data[$prefix . 'path_string'];
        $location->modifiedSubLocation = $data[$prefix . 'modified_subnode'];
        $location->mainLocationId = $data[$prefix . 'main_node_id'];
        $location->depth = $data[$prefix . 'depth'];
        $location->sortField = $data[$prefix . 'sort_field'];
        $location->sortOrder = $data[$prefix . 'sort_order'];

        return $location;
    }
}
