<?php
/**
 * Abstract Domain object, required for generic persistent objects
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Domain object
 */
namespace ezx\content\Abstracts;
abstract class ContentModel extends \ezp\base\AbstractModel
{

    /**
     * Constant for string type in {@see definition()}
     * @var int
     */
    const TYPE_STRING = 1;

    /**
     * Constant for int type in {@see definition()}
     * @var int
     */
    const TYPE_INT    = 2;

    /**
     * Constant for float type in {@see definition()}
     * @var int
     */
    const TYPE_FLOAT  = 3;

    /**
     * Constant for array type in {@see definition()}
     * @var int
     */
    const TYPE_ARRAY  = 4;

    /**
     * Constant for object type in {@see definition()}
     * @var int
     */
    const TYPE_OBJECT = 5;

    /**
     * Constant for bool type in {@see definition()}
     * @var int
     */
    const TYPE_BOOL   = 6;

    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @throws \InvalidArgumentException When trying to set invalid properties on this object
     * @param array $properties
     * @return ContentModel Return $this
     */
    public function fromHash( array $properties )
    {
        foreach ( $this->readableProperties as $property => $member )
        {
            if ( !$member || !isset( $properties[$property] ) )
                continue;

            $this->$property = $properties[$property];
        }

        foreach ( $this->dynamicProperties as $property => $member )
        {
            if ( !$member || !isset( $properties[$property] ) )
                continue;

            $value = $this->__get( $property );
            if ( $value instanceof ContentModel )
            {
                $value->fromHash( $properties[$property] );
                continue;
            }

            if ( !is_array( $value ) )
                continue;

            foreach ( $value as $key => $item )
            {
                if ( isset( $properties[$property][$key] ) && $item instanceof ContentModel )
                    $item->fromHash( $properties[$property][$key] );
            }
        }
        return $this;
    }

    /**
     * Get properties with hash, name is same as used in ezc Persistent
     *
     * @param bool $internals Include internal data like id and version in hash if true
     * @return array
     */
    public function toHash( $internals = false )
    {
        $hash = array();
        foreach ( $this->readableProperties as $property => $member )
        {
            if ( !$member && !$internals )
                continue;

            $hash[$property] = $this->$property;
        }

        foreach ( $this->dynamicProperties as $property => $member )
        {
            if ( !$member )
                continue;

            $value = $this->__get( $property );
            if ( $value instanceof ContentModel )
            {
                $hash[$property] = $value->toHash( $internals );
                continue;
            }

            if ( !$value instanceof \ArrayAccess && !is_array( $value ) )
                continue;

            $hash[$property] = array();
            foreach ( $value as $key => $item )
            {
                if ( $item instanceof ContentModel )
                    $hash[$property][$key] = $item->toHash( $internals );
            }
        }
        return $hash;
    }
}
