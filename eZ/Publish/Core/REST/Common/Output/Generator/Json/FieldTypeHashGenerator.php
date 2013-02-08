<?php
/**
 * File containing the Json FieldTypeHashGenerator class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object $parent
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    public function generateHashValue( $parent, $hashElementName, $hashValue )
    {
        $parent->$hashElementName = $this->generateValue( $parent, $hashValue );
    }

    /**
     * Generates and returns a value based on $hashValue type, with $parent (
     * if the type of $hashValue supports it)
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object $parent
     * @param mixed $value
     *
     * @return mixed
     */
    protected function generateValue( $parent, $value )
    {
        switch ( ( $hashValueType = gettype( $value ) ) )
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
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object $parent
     * @param array $value
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object
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
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object $parent
     * @param array $listArray
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject
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
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\ArrayObject|\eZ\Publish\Core\REST\Common\Output\Generator\Json\Object $parent
     * @param array $hashArray
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Json\Object
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
}
