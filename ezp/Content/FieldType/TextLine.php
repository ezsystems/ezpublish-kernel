<?php
/**
 * File containing the TextLine class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Content\FieldType;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class TextLine extends FieldType
{
    protected $fieldTypeString = 'ezstring';
    protected $defaultValue = '';
    protected $isSearchable = true;
    protected $isTranslateable = true;

    protected $allowedSettings = array( 'maxStringLength' => null );

    public function __construct()
    {
        parent::__construct();
    }

    protected function parseValue( $inputValue )
    {
    }

    public function setValue( $inputValue )
    {
    }

    public function getValue()
    {
    }

    public function getTypeHandler()
    {
    }

}
