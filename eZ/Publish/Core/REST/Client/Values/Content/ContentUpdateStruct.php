<?php

/**
 * File containing the ContentUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
 */
class ContentUpdateStruct extends APIContentUpdateStruct
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Field[] */
    protected $fields = array();

    /**
     * Adds a field to the field collection.
     * This method could also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param bool|string $languageCode If not given on a translatable field the initial language is used,
     */
    public function setField($fieldDefIdentifier, $value, $languageCode = null)
    {
        $this->fields[] = new Field(
            array(
                'fieldDefIdentifier' => $fieldDefIdentifier,
                'value' => $value,
                'languageCode' => $languageCode,
            )
        );
    }
}
