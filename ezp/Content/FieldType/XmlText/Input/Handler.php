<?php
/**
 * File containing the ezp\Content\FieldType\XmlText\Input\Handler interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText\Input;
/**
 * Interface for XmlText input handlers
 */
interface Handler
{
    /**
     * Checks if XML is valid
     * @return bool
     */
    public function isXmlValid();

    /**
     * Callback that gets a location from its path
     * @param string $locationPath
     * @return \ezp\Content\Location
     */
    public function getLocationByPath( $locationPath );

    /**
     * Callback that gets a location from its id
     * @param mixed $locationId
     * @return \ezp\Content\Location
     */
    public function getLocationById( $locationId );

    /**
     * Registers an external URL
     * @param string $url
     * @return Url
     * @todo Implement & Document
     */
    public function registerUrl( $url );
}

?>