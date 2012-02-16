<?php
/**
 * File containing the ezimage Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image;
use eZ\Publish\Core\Repository\FieldType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Content\Field,
    ezp\Base\Exception\BadFieldTypeInput,
    ezp\Base\Observable;

/**
 * The Image field type
 */
class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'ezimage';

    /**
     * @see eZ\Publish\Core\Repository\FieldType::$allowedValidators
     */
    protected $allowedValidators = array(
        'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Value
     */
    public function getDefaultValue()
    {
        return new Value;
    }

    protected function canParseValue( BaseValue $inputValue )
    {
        if ( $inputValue instanceof Value )
        {
            if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
                throw new BadFieldTypeInput( $inputValue, get_class( $this ) );

            return $inputValue;
        }

        throw new InvalidArgumentType( 'value', 'eZ\\Publish\\Core\\Repository\\FieldType\\Image\\Value' );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType::getSortInfo()
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return false;
    }

    /**
     *
     * @param \ezp\Base\Observable $subject
     * @param \eZ\Publish\Core\Repository\FieldType\Image\Value $value
     */
    protected function onFieldSetValue( Observable $subject, BaseValue $value )
    {
        parent::onFieldSetValue( $subject, $value );
        if ( $subject instanceof Field )
        {
            // Here inject $fieldId, $contentId, $versionNo, $status (publication status), $languageCode, $isTranslatable
            $this->getValue()->setState(
                array(
                    'fieldId' => $subject->id,
                    'contentId' => $subject->getVersion()->contentId,
                    'versionNo' => $subject->getVersion()->versionNo,
                    'versionStatus' => $subject->getVersion()->status,
                    'languageCode' => $subject->language,
                    'isTranslatable' => $subject->getFieldDefinition()->isTranslatable
                )
            );
        }
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
        throw new \Exception( "Not implemented yet" );
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
        throw new \Exception( "Not implemented yet" );
        return $value->value;
    }
}
