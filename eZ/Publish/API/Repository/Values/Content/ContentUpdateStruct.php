<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating the fields of a content object draft
 *
 * @property-write array $fields
 */
abstract class ContentUpdateStruct extends ValueObject
{
    /**
     * The language code of the version. In 4.x this code will be used as the language code of the translation
     * (which is shown in the admin interface).
     * It is also used as default language for added fields.
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * Adds a field to the field collection.
     * This method could also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param boolean|string $languageCode If not given on a translatable field the initial language is used,
     */
    abstract public function setField( $fieldDefIdentifier, $value, $languageCode = null );
}
