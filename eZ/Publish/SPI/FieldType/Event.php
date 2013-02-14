<?php
/**
 * File containing the Event base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType;

/**
 * Abstract base class for FieldType events
 *
 * An instance of a derived class is given to {@link
 * eZ\Publish\SPI\FieldType\EventListener::handleEvent()}. The derived class name
 * identified the occurred event. The properties of the class give the needed
 * event context.
 *
 * @todo Add VersionInfo parameter
 */
abstract class Event
{
    /**
     * Definition of $field
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public $fieldDefinition;

    /**
     * Field the event occurred on
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field
     */
    public $field;

    /**
     * VersionInfo of the Content the affected field belongs to
     *
     * Value is null in case of pre create events!
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    public $versionInfo;
}
