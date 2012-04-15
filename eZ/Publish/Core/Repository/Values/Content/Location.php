<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Location class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

/**
 * This class represents a location in the repository
 */
class Location extends APILocation
{
    /**
     * content info of the content object of this location
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * returns the content info of the content object of this location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * Magic getter for retrieving convenience properties
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get( $property )
    {
        switch ( $property )
        {
            case 'contentId':
                return $this->contentInfo->id;
        }

        return parent::__get( $property );
    }

    /**
     * Magic isset for singaling existence of convenience properties
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset( $property )
    {
        if ( $property === 'contentId' )
            return true;

        return parent::__isset( $property );
    }
}
