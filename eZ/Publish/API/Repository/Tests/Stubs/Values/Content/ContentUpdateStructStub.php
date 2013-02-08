<?php
/**
 * File containing the ContentUpdateStructStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
 */
class ContentUpdateStructStub extends ContentUpdateStruct
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected $fields = array();

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
    public function setField( $fieldDefIdentifier, $value, $languageCode = null )
    {
        $this->fields[] = new Field(
            array(
                'fieldDefIdentifier' => $fieldDefIdentifier,
                'value' => $value,
                'languageCode' => $languageCode
            )
        );
    }
}
