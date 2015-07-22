<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\TranslationValues class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This value object is used for adding a translation to a version.
 *
 * @property-write FieldCollection $fields
 */
abstract class TranslationValues extends ValueObject
{
    /**
     * Adds a translated field to the field collection in the given language
     * This method is also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier] = $value is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     */
    abstract public function setField($fieldDefIdentifier, $value);
}
