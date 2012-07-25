<?php
/**
 * File containing the LocationList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * Location list view model
 */
class LocationList
{
    /**
     * Locations
     *
     * @var array
     */
    public $locations;

    /**
     * ID of content this locations belong to
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Construct
     *
     * @param array $locations
     * @param mixed $contentId
     */
    public function __construct( array $locations, $contentId )
    {
        $this->locations = $locations;
        $this->contentId = $contentId;
    }
}

