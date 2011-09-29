<?php
/**
 * File containing the ezimage Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Content\FieldType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\Field,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Observable;

/**
 * The Image field type
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'ezimage';
    const IS_SEARCHABLE = false;

    /**
     * @see ezp\Content\FieldType::$allowedValidators
     */
    protected $allowedValidators = array(
        'ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * @return \ezp\Content\FieldType\Image\Value
     */
    protected function getDefaultValue()
    {
        return new Value;
    }

    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
                throw new BadFieldTypeInput( $inputValue, get_class() );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\Image\\Value' );
    }

    /**
     * @see \ezp\Content\FieldType::getSortInfo()
     * @return bool
     */
    protected function getSortInfo()
    {
        return false;
    }

    /**
     *
     * @param \ezp\Base\Observable $subject
     * @param \ezp\Content\FieldType\Image\Value $value
     */
    protected function onFieldSetValue( Observable $subject, BaseValue $value )
    {
        parent::onFieldSetValue( $subject, $value );
        if ( $subject instanceof Field )
        {
            // Here inject $fieldId, $contentId, $versionNo, $languageCode, $isTranslatable
        }
    }
}
