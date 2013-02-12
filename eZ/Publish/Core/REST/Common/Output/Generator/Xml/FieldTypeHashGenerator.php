<?php
/**
 * File containing the FieldTypeSerializer base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output\Generator\Xml;

use eZ\Publish\Core\REST\Common\Output\Generator\Xml as XmlWriter;

class FieldTypeHashGenerator
{
    /**
     * Generates the field type value $hashValue into the $writer creating an
     * element with $hashElementName as its parent
     *
     * @param \XmlWriter $writer
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    public function generateHashValue( \XMLWriter $writer, $hashElementName, $hashValue )
    {
        $this->generateValue( $writer, $hashValue, null, $hashElementName );
    }

    /**
     * Generates $value into a serialized representation
     *
     * @param \XmlWriter $writer
     * @param mixed $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateValue( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        switch ( ( $hashValueType = gettype( $value ) ) )
        {
            case 'NULL':
                $this->generateNullValue( $writer, $key, $elementName );
                break;

            case 'boolean':
                $this->generateBooleanValue( $writer, $value, $key, $elementName );
                break;

            case 'integer':
                $this->generateIntegerValue( $writer, $value, $key, $elementName );
                break;

            case 'double':
                $this->generateFloatValue( $writer, $value, $key, $elementName );
                break;

            case 'string':
                $this->generateStringValue( $writer, $value, $key, $elementName );
                break;

            case 'array':
                $this->generateArrayValue( $writer, $value, $key, $elementName );
                break;

            default:
                throw new \Exception( 'Invalid type in field value hash: ' . $hashValueType );
        }
    }

    /**
     * Generates an array value from $value
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateArrayValue( \XmlWriter $writer, $value, $key, $elementName = 'value' )
    {
        if ( $this->isNumericArray( $value ) )
        {
            $this->generateListArray( $writer, $value, $key, $elementName );
        }
        else
        {
            $this->generateHashArray( $writer, $value, $key, $elementName );
        }
    }

    /**
     * Generates $value as a hash of value items
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateHashArray( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );

        foreach ( $value as $hashKey => $hashItemValue )
        {
            $this->generateValue( $writer, $hashItemValue, $hashKey );
        }

        $writer->endElement();
    }

    /**
     * Generates $value as a list of value items
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateListArray( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );

        foreach ( $value as $listItemValue )
        {
            $this->generateValue( $writer, $listItemValue );
        }

        $writer->endElement();
    }

    /**
     * Checks if the given $value is a purely numeric array
     *
     * @param array $value
     *
     * @return boolean
     */
    protected function isNumericArray( array $value )
    {
        foreach ( array_keys( $value ) as $key )
        {
            if ( is_string( $key ) )
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Generates a null value
     *
     * @param \XmlWriter $writer
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateNullValue( \XmlWriter $writer, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );
        // @todo: xsi:type?
        $writer->endElement();
    }

    /**
     * Generates a boolean value
     *
     * @param \XmlWriter $writer
     * @param boolean $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateBooleanValue( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );
        $writer->text( $value ? 'true' : 'false' );
        $writer->endElement();
    }

    /**
     * Generates a integer value
     *
     * @param \XmlWriter $writer
     * @param int $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateIntegerValue( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );
        $writer->text( $value );
        $writer->endElement();
    }

    /**
     * Generates a float value
     *
     * @param \XmlWriter $writer
     * @param float $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateFloatValue( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );
        $writer->text( sprintf( '%F', $value ) );
        $writer->endElement();
    }

    /**
     * Generates a string value
     *
     * @param \XmlWriter $writer
     * @param string $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateStringValue( \XmlWriter $writer, $value, $key = null, $elementName = 'value' )
    {
        $writer->startElement( $elementName );
        $this->generateKeyAttribute( $writer, $key );
        $writer->text( $value );
        $writer->endElement();
    }

    /**
     * Generates a key attribute with $key as the value, if $key is not null
     *
     * @param \XmlWriter $writer
     * @param string|null $key
     */
    protected function generateKeyAttribute( \XmlWriter $writer, $key = null )
    {
        if ( $key !== null )
        {
            $writer->startAttribute( 'key' );
            $writer->text( $key );
            $writer->endAttribute();
        }
    }
}
