<?php
/**
 * File containing the Event base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType\FieldStorage;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Interface for FieldType events
 *
 * An instance of a derived class is given to {@link
 * eZ\Publish\SPI\FieldType\EventListener::handleEvent()}. The derived class name
 * identified the occurred event. The properties of the class give the needed
 * event context.
 */
interface Event
{
    /**
     * @return Field
     */
    public function getField();

    /**
     * @param Field $field
     */
    public function setField( Field $field );

    /**
     * @return VersionInfo
     */
    public function getVersionInfo();

    /**
     * @param VersionInfo $versionInfo
     */
    public function setVersionInfo( VersionInfo $versionInfo );
}
