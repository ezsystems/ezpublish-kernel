<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\Persistence\Tests\InMemoryEngine;

/**
 * The Storage Engine backend for in memory storage
 * Reads input from js files in provided directory and fills in memory db store.
 *
 * The in memory db store and also json representation have a one to one mapping to defined value objects.
 * But only their plain properties, associations are not handled and all data is stored in separate "buckets" similar
 * to how it would be in a RDBMS servers.
 *
 * @package ezp
 * @subpackage persistence
 */
class Backend
{

    /**
     * @var array
     */
    protected $data = array();

    /**
     * Construct backend by reading data in data.json
     */
    public function __construct()
    {
        $this->data = json_decode( file_get_contents( __DIR__ . '/data.json' ), true ) + $this->data;
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
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

        $data['id'] = count( $this->data[$type] ) +1;
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
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

        $list = $this->find( $type, array( 'id' => $id ) );
        if ( isset( $list[0] ) )
            return $list[0];
        return null;
    }

    /**
     * Find data from in memory store for a specific type that matches criteria (empty array will match all)
     *
     * @param string $type
     * @param array $criteria A simple array criteria with property => value to match against.
     * @return object[]
     */
    public function find( $type, array $criteria = array() )
    {
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

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
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

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
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

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
        if ( !isset( $this->data[$type] ) )
            throw new \ezp\Base\Exception\InvalidArgumentValue( 'type', $type );

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
        if ( !isset( $this->data[$type] ) )
            return array();

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
     * Creates Value object / struct based on array value from Backend.
     *
     * @internal
     * @param string $type
     * @param array $data
     * @return object
     */
    protected function toValue( $type, array $data )
    {
        $className = "\\ezp\\Persistence\\$type";
        $obj = new $className;
        foreach ( $obj as $prop => $value )
        {
            if ( isset( $data[$prop] ) )
                $obj->$prop = $data[$prop];
        }
        return $obj;
    }
}
