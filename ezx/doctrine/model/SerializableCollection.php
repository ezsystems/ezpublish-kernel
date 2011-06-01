<?php
/**
 * Content (content object) field map object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Content model object
 *
 * @internal
 */
namespace ezx\doctrine\model;
class SerializableCollection extends \Doctrine\Common\Collections\ArrayCollection implements Interface_Serializable
{
    /**
     * Static variant of setState
     *
     * @param array $properties
     * @return FieldMap
     */
    public static function __set_state( array $properties )
    {
        $class = new static( array() );
        return $class->setState( $properties );
    }
    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return Content Return $this
     */
    public function setState( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( $value instanceof Interface_Serializable )
            {
                $this->set( $property, $value );
            }
            else if ( $this->containsKey( $property ) )
            {
                $element = $this->get( $property );
                if( $element instanceof Interface_Serializable )
                    $element->setState( $value );
                else
                   $this->set( $property, $value );
            }
        }
        return $this;
    }

    /**
     * Get properties with hash, fn name is same as used in ezc Persistent
     *
     * @return array
     */
    public function getState()
    {
        $hash = array();
        foreach( $this->toArray() as $property => $value )
        {
            if ( $value instanceof Interface_Serializable )
                $hash[$property] = $value->getState();
            else
                $hash[$property] = $value;
        }
        return $hash;
    }
}
