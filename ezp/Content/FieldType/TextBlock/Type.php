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
    ezp\Content\FieldType\TextLine\Type as TextLine,
    ezp\Persistence\Content\FieldValue;

/**
 * The TextBlock field type.
 *
 * Represents a larger body of text, such as text areas.
 */
class Type extends TextLine
{
    const FIELD_TYPE_IDENTIFIER = "eztext";

    protected $defaultValue = '';
    protected $isSearchable = true;

    protected $allowedSettings = array( 'textColumns' => null );
    protected $allowedValidators = array();

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
