<?php
/**
 * File containing the Url class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Url;
use eZ\Publish\Core\Repository\FieldType\FieldType,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The Url field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $settingSchema = array(
        'defaultText' => ''
    );

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $link as value.
     *
     * @param string $link
     * @return \eZ\Publish\Core\Repository\FieldType\Url\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $link )
    {
        return new Value( $link );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezurl";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @todo Is a default value really possible with this type?
     *       Shouldn't an exception be used?
     * @return \eZ\Publish\Core\Repository\FieldType\Url\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( "" );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Url\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Url\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\Url\\Value',
                $inputValue
           );
        }

        if ( !is_string( $inputValue->link ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->link',
                'string',
                $inputValue->link
           );
        }

        if ( isset( $inputValue->text ) && !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
           );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo Sort seems to not be supported by this FieldType, is this handled correctly?
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Url\Value $value
     */
    public function fromHash( $hash )
    {
        if ( isset( $hash["text"] ) )
            return new Value( $hash["link"], $hash["text"] );

        return new Value( $hash["link"] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Url\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array( "link" => $value->link, "text" => $value->text );
    }
}
