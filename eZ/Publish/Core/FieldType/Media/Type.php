<?php
/**
 * File containing the Media Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Media;
use eZ\Publish\Core\FieldType\BinaryBase\Type as BaseType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends BaseType
{
    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezmedia";
    }

    /**
     * Creates a specific value of the derived class from $inputValue
     *
     * @param array $inputValue
     * @return Value
     */
    protected function createValue( array $inputValue )
    {
        return new Value( $inputValue );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Media\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Media\Value
     */
    public function acceptValue( $inputValue )
    {
        $inputValue = parent::acceptValue( $inputValue );

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Media\\Value',
                $inputValue
            );
        }

        if ( !is_bool( $inputValue->hasController ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->hasController',
                'bool',
                $inputValue->hasController
            );
        }
        if ( !is_bool( $inputValue->autoplay ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->autoplay',
                'bool',
                $inputValue->autoplay
            );
        }
        if ( !is_bool( $inputValue->loop ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->loop',
                'bool',
                $inputValue->loop
            );
        }

        if ( !is_int( $inputValue->height ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->height',
                'int',
                $inputValue->height
            );
        }
        if ( !is_int( $inputValue->width ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->width',
                'int',
                $inputValue->width
            );
        }

        return $inputValue;
    }

    /**
     * Attempts to complete the data in $value
     *
     * @param Value $value
     * @return void
     */
    protected function completeValue( $value )
    {
        parent::completeValue( $value );

        if ( !isset( $value->hasController ) )
        {
            $value->hasController = false;
        }
        if ( !isset( $value->autoplay ) )
        {
            $value->autoplay = false;
        }
        if ( !isset( $value->loop ) )
        {
            $value->loop = false;
        }

        if ( !isset( $value->height ) )
        {
            $value->height = 0;
        }
        if ( !isset( $value->width ) )
        {
            $value->width = 0;
        }
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Media\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        $hash = parent::toHash( $value );

        if ( $hash === null )
        {
            return $hash;
        }

        $hash['hasController'] = $value->hasController;
        $hash['autoplay'] = $value->autoplay;
        $hash['loop'] = $value->loop;
        $hash['width'] = $value->width;
        $hash['height'] = $value->height;

        return $hash;
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        $result = parent::fromPersistenceValue( $fieldValue );

        if ( $result === null )
        {
            // empty value
            return null;
        }

        $result->hasController = ( isset( $fieldValue->externalData['hasController'] )
            ? $fieldValue->externalData['hasController']
            : false );
        $result->autoplay = ( isset( $fieldValue->externalData['autoplay'] )
            ? $fieldValue->externalData['autoplay']
            : false );
        $result->loop = ( isset( $fieldValue->externalData['loop'] )
            ? $fieldValue->externalData['loop']
            : false );
        $result->height = ( isset( $fieldValue->externalData['height'] )
            ? $fieldValue->externalData['height']
            : 0 );
        $result->width = ( isset( $fieldValue->externalData['width'] )
            ? $fieldValue->externalData['width']
            : 0 );

        return $result;
    }
}
