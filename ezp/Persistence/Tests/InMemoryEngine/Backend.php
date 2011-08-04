<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Base\Exception\InvalidArgumentValue;

/**
 * The Storage Engine backend for in memory storage
 * Reads input from js files in provided directory and fills in memory db store.
 *
 * The in memory db store and also json representation have a one to one mapping to defined value objects.
 * But only their plain properties, associations are not handled and all data is stored in separate "buckets" similar
 * to how it would be in a RDBMS servers.
 *
 */
class Backend
{

    /**
     * @var array
     */
    protected $data = array();

    /**
     * Construct backend and assign data
     *
     * Use:
     *     new Backend( json_decode( file_get_contents( __DIR__ . '/data.json' ), true ) );
     *
     * @param array $data Data where key is type like "Content" or "Content\\Type" which then have to map to
     *                    Value objects in ezp\Persistence\*, data is an array of hash values with same structure as
     *                    the corresponding value object.
     *                    Foreign keys: In some cases value objects does not contain these as they are internal, so this
     *                                  needs to be handled in InMemory handlers by assigning keys like "_typeId" on
     *                                  Type\FieldDefintion hash values for instance. These will be stored and can be
     *                                  matched with find(), but will not be returned as part of VO so purely internal.
     */
    public function __construct( array $data )
    {
        $this->data = $data + $this->data;
    }

    /**
     * Creates data in in memory store
     *
     * @param string $type
     * @param int|string $id
     * @param array $data
     * @return object
     */
    public function create( $type, array $data )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $data['id'] = $this->getNextId( $type );
        $this->data[$type][] = $data;
        return $this->toValue( $type, $data );
    }

    /**
     * Reads data from in memory store
     *
     * @param string $type
     * @param int|string $id
     * @return object|null
     */
    public function load( $type, $id )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, array( 'id' => $id ) );
        if ( empty( $items ) )
            return null;

        return $this->toValue( $type, $this->data[$type][ $items[0] ] );
    }

    /**
     * Find data from in memory store for a specific type that matches criteria (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $criteria A simple array criteria with property => value to match against.
     * @return object[]
     */
    public function find( $type, array $criteria = array() )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, $criteria );
        foreach ( $items as $key => $typeIndex )
            $items[$key] = $this->toValue( $type, $this->data[$type][$typeIndex] );
        return $items;
    }

    /**
     * Updates data in in memory store
     *
     * @param string $type
     * @param int|string $id
     * @param array $data
     * @return bool False if data does not exist and can not be updated
     */
    public function update( $type, $id, array $data )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, array( 'id' => $id ) );
        if ( empty( $items ) )
            return false;

        foreach ( $items as $typeIndex )
            $this->data[$type][$typeIndex] = $data + $this->data[$type][$typeIndex];
        return true;
    }

    /**
     * Deletes data in in memory store
     *
     * @param string $type
     * @param int|string $id
     * @return bool False if data does not exist and can not be deleted
     */
    public function delete( $type, $id )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, array( 'id' => $id ) );
        if ( empty( $items ) )
            return false;

        foreach ( $items as $typeIndex )
            unset( $this->data[$type][$typeIndex] );
        return true;
    }

    /**
     * Find count of objects of a given type matching a simple criteria (empty array will match all)
     *
     * @param string $type
     * @param array $criteria A simple array criteria with property => value to match against.
     * @return int
     */
    public function count( $type, array $criteria = array() )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        return count( $this->findKeys( $type, $criteria ) );
    }

    /**
     * Find keys for data from in memory store for a specific type that matches criteria
     *
     * @internal
     * @param string $type
     * @param array $criteria A simple array criteria with property => value to match against.
     * @return int[]
     */
    protected function findKeys( $type, array $criteria )
    {
        $keys = array();
        foreach ( $this->data[$type] as $key => $hash )
        {
            foreach ( $criteria as $property => $value )
            {
                if ( !isset( $hash[$property] ) || $hash[$property] != $value )
                    continue 2;
            }
            $keys[] = $key;
        }
        return $keys;
    }

    /**
     * Finds the max id number and that +1
     *
     * Makes sure no id conflicts occur if data for some reason contains gaps in id numbers.
     *
     * @param $type
     * @return int
     */
    private function getNextId( $type )
    {
        $id = 0;
        foreach ( $this->data[$type] as $hash )
        {
            $id = max( $id, $hash['id'] );
        }
        return $id + 1;
    }

    /**
     * Creates Value object / struct based on array value from Backend.
     *
     * @internal
     * @param string $type
     * @param array $data
     * @return object
     */
    protected function toValue( $type, array $data )
    {
        $className = "ezp\\Persistence\\$type";
        $obj = new $className;
        foreach ( $obj as $prop => &$value )
        {
            if ( isset( $data[$prop] ) )
                $value = $data[$prop];
        }
        return $obj;
    }
}
