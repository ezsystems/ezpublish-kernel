<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * This class is used for updating the fields of a content object draft.
 *
 * @property \eZ\Publish\API\Repository\Values\Content\Field[] $fields
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class ContentUpdateStruct extends APIContentUpdateStruct
{
    /**
     * Field collection.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public $fields = [];

    /**
     * Adds a field to the field collection.
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
            [
                'fieldDefIdentifier' => $fieldDefIdentifier,
                'value' => $value,
                'languageCode' => $language,
            ]
        );
    }
}
