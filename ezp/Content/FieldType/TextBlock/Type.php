<?php
/**
 * File containing the TextBlock class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\TextBlock;
use ezp\Content\FieldType,
    ezp\Content\FieldType\TextLine\Type as TextLine;

/**
 * The TextBlock field type.
 *
 * Represents a larger body of text, such as text areas.
 */
class Type extends TextLine
{
    const FIELD_TYPE_IDENTIFIER = "eztext";
    const IS_SEARCHABLE = true;

    protected $allowedSettings = array( 'textColumns' => null );
    protected $allowedValidators = array();

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\TextBlock\Value
     */
    protected function getDefaultValue()
    {
        return new Value( "" );
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo()
    {
        return array( 'sort_key_string' => '' );
    }
}
