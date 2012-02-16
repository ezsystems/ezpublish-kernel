<?php
/**
 * File containing the TextBlock class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\TextBlock;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\FieldType\TextLine\Type as TextLine;

/**
 * The TextBlock field type.
 *
 * Represents a larger body of text, such as text areas.
 */
class Type extends TextLine
{
    const FIELD_TYPE_IDENTIFIER = "eztext";
    const IS_SEARCHABLE = true;

    protected $allowedValidators = array();

    protected $allowedSettings = array( 'textColumns' => 10 );

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\TextBlock\Value
     */
    public function getDefaultValue()
    {
        return new Value( "" );
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @return array
     */
    protected function getSortInfo( BaseValue $value )
    {
        return array( 'sort_key_string' => '' );
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash( BaseValue $value )
    {
        return $value->text;
    }
}
