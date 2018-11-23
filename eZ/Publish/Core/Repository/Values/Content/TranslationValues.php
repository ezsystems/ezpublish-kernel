<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\TranslationValues as APITranslationValues;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * This value object is used for adding a translation to a version.
 *
 * @property \eZ\Publish\API\Repository\Values\Content\Field[] $fields
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class TranslationValues extends APITranslationValues
{
    /**
     * Field collection.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public $fields = array();

    /**
     * Adds a translated field to the field collection in the given language
     * This method could also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param string|null $language If not given on a translatable field the initial language is used,
     */
    public function setField($fieldDefIdentifier, $value, $language = null)
    {
        $this->fields[] = new Field(
            array(
                'fieldDefIdentifier' => $fieldDefIdentifier,
                'value' => $value,
                'languageCode' => $language,
            )
        );
    }
}
