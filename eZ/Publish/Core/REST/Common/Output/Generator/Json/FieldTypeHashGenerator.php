<?php
/**
 * File containing the Json FieldTypeHashGenerator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output\Generator\Json;

class FieldTypeHashGenerator
{
    /**
     * Generates the field type value $hashValue as a child of the given Object
     * using $hashElementName as the property name
     *
     * @param eZ\Publish\Core\REST\Common\Output\Generator\Json\$parent
     * @param string $hashElementName
     * @param mixed $hashValue
     * @return void
     */
    public function generateHashValue( $parent, $hashElementName, $hashValue )
    {
        $parent->$hashElementName = $this->generateValue( $parent, $hashValue );
    }

    /**
     * Generates and returns a value based on $hashValue type, with $parent (
     * if the type of $hashValue supports it)
     *
     * @param Object|ArrayObject $parent
     * @param mixed $hashValue
     * @return mixed
     */
    protected function generateValue( $parent, $value )
    {
        switch( ( $hashValueType = gettype( $value ) ) )
        {
            case 'NULL':
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':

                // Will be handled accordingly on serialization
                return $value;
                break;

            case 'array':
                return $this->generateArrayValue( $parent, $value );
                break;

            default:
                throw new \Exception( 'Invalid type in field value hash: ' . $hashValueType );
        }
    }

    /**
     * Generates and returns a JSON structure (array or object) depending on $value type
     * with $parent
     *
     * If $type only contains numeric keys, the resulting structure will be an
     * JSON array, otherwise a JSON object
     *
     * @param Object|ArrayObject $parent
     * @param array $value
     * @return Object|ArrayObject
     */
    protected function generateArrayValue( $parent, array $value )
    {
        if ( $this->isNumericArray( $value ) )
        {
            return $this->generateListArray( $parent, $value );
        }
        else
        {
            return $this->generateHashArray( $parent, $value );
        }
    }

    /**
     * Generates a JSON array from the given $hashArray with $parent
     *
     * @param Object|ArrayObject $parent
     * @param array $listArray
     * @return void
     */
    protected function generateListArray( $parent, array $listArray )
    {
        $arrayObject = new ArrayObject( $parent );
        foreach ( $listArray as $listItem )
        {
            $arrayObject[] = $this->generateValue( $arrayObject, $listItem );
        }
        return $arrayObject;
    }

    /**
     * Generates a JSON object from the given $hashArray with $parent
     *
     * @param Object|ArrayObject $parent
     * @param array $hashArray
     * @return Object
     */
    protected function generateHashArray( $parent, array $hashArray )
    {
        $object = new Object( $parent );
        foreach ( $hashArray as $hashKey => $hashItem )
        {
            $object->$hashKey = $this->generateValue( $object, $hashItem );
        }
        return $object;
    }

    /**
     * Checks if the given $value is a purely numeric array
     *
     * @param array $value
     * @return bool
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
}
