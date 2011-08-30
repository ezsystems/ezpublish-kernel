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
     * @todo Make use of $prefix
     */
    public function createLocationFromRow( array $data, $prefix = '' )
    {
        $location = new Location();

        $location->id = $data['node_id'];
        $location->priority = $data['priority'];
        $location->hidden = $data['is_hidden'];
        $location->invisible = $data['is_invisible'];
        $location->remoteId = $data['remote_id'];
        $location->contentId = $data['contentobject_id'];
        $location->parentId = $data['parent_node_id'];
        $location->pathIdentificationString = $data['path_identification_string'];
        $location->pathString = $data['path_string'];
        $location->modifiedSubLocation = $data['modified_subnode'];
        $location->mainLocationId = $data['main_node_id'];
        $location->depth = $data['depth'];
        $location->sortField = $data['sort_field'];
        $location->sortOrder = $data['sort_order'];

        return $location;
    }
}
