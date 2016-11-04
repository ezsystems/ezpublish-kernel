<?php

/**
 * File containing the Event base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType;

/**
 * Abstract base class for FieldType events.
 *
 * An instance of a derived class is given to {@link * eZ\Publish\SPI\FieldType\EventListener::handleEvent()}. The derived class name
 * identified the occurred event. The properties of the class give the needed
 * event context.
 *
 * @todo Add VersionInfo parameter
 * @deprecated (Not implemented)
 */
abstract class Event
{
    /**
     * Definition of $field.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public $fieldDefinition;

    /**
     * Field the event occurred on.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field
     */
    public $field;

    /**
     * VersionInfo of the Content the affected field belongs to.
     *
     * Value is null in case of pre create events!
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    public $versionInfo;
}
