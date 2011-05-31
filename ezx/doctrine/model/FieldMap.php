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
class FieldMap extends SerializableCollection
{
    /**
     * Overrides offsetSet to deal directly with Field Value object
     *
     * @throws \InvalidArgumentException
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        if ( $offset !== null && $this->containsKey( $offset ) )
            return $this->get( $offset )->value = $value;
        else
            throw new \InvalidArgumentException( "{$offset} is not a valid property on " . __CLASS__ );
    }

    /**
     * Overrides offsetGet to deal directly with Field Value object
     *
     * @see get()
     * @param string $offset
     */
    public function offsetGet( $offset )
    {
        return $this->get( $offset )->value;
    }

    /**
     * Unset a key in array hash, but not supported on this class.
     *
     * @throws \InvalidArgumentException
     * @param string $offset
     */
    public function offsetUnset( $offset )
    {
        throw new \InvalidArgumentException( "Un-setting fields is not supported on " . __CLASS__ );
    }
}
