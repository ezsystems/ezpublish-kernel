<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\Logic,
    ezp\Persistence\ValueObject;

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
        else if ( isset( $items[1] ) )
            throw new Logic( $type, "more then one item exist with id: {$id}" );

        return $this->toValue( $type, $this->data[$type][ $items[0] ] );
    }

    /**
     * Find data from in memory store for a specific type that matches $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @param array $joinInfo Optional info on how to join in other objects to become part of a
     *                        aggregate where $type is root.
     *                        Format:
     *                            array( '<property>' => array(
     *                                'type' => '<foreign-type>',
     *                                'match' => array( '<foreign-key-property>' => '<key-property>' ) ),
     *                                ['sub' => <$joinInfo>]
     *                            )
     *                        Example (joining Location when finding Content):
     *                            array( 'locations' => array(
     *                                'type' => 'Content\\Location',
     *                                'match' => array( 'contentId' => 'id' ) )
     *                            )
     *                        Value of 'sub' follows exactly same format as $joinInfo allowing recursive joining.
     * @return object[]
     */
    public function find( $type, array $match = array(), array $joinInfo = array() )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, $match );
        foreach ( $items as $key => $index )
        {
            $items[$key] = $this->toValue( $type, $this->data[$type][$index], $joinInfo );
        }
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
        return $this->updateByMatch( $type, array( 'id' => $id ), $data );
    }

    /**
     * Updates data in in memory store by match
     *
     * Useful in cases where a specific state of an object should be updated,
     * Type with version=0 for instance.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @param array $data
     * @return bool False if data does not exist and can not be updated
     */
    public function updateByMatch( $type, array $match, array $data )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, $match );
        if ( empty( $items ) )
            return false;

        foreach ( $items as $index )
            $this->data[$type][$index] = $data + $this->data[$type][$index];
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
        return $this->deleteByMatch( $type, array( 'id' => $id ) );
    }

    /**
     * Deletes data in in memory store
     *
     * Useful in cases where a specific state of an object should be updated,
     * Type with version=0 for instance.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @return bool False if data does not exist and can not be deleted
     */
    public function deleteByMatch( $type, array $match )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = $this->findKeys( $type, $match );
        if ( empty( $items ) )
            return false;

        foreach ( $items as $index )
            unset( $this->data[$type][$index] );
        return true;
    }

    /**
     * Find count of objects of a given type matching a simple $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @return int
     */
    public function count( $type, array $match = array() )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        return count( $this->findKeys( $type, $match ) );
    }

    /**
     * Find keys for data from in memory store for a specific type that matches $match
     *
     * @internal
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @return int[]
     */
    protected function findKeys( $type, array $match )
    {
        $keys = array();
        foreach ( $this->data[$type] as $key => $hash )
        {
            foreach ( $match as $property => $value )
            {
                if ( !isset( $hash[$property] ) )
                    continue 2;

                if ( is_array( $hash[$property] ) )
                {
                    if ( !in_array( $value, $hash[$property] ) )
                        continue 2;
                }
                else if ( $hash[$property] != $value )
                {
                    continue 2;
                }
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
     * Creates Value object based on array value from Backend.
     *
     * @internal
     * @param string $type
     * @param array $data
     * @param array $joinInfo See {@link find()}
     * @return object
     */
    protected function toValue( $type, array $data, array $joinInfo = array() )
    {
        $className = "ezp\\Persistence\\$type";
        $obj = new $className;
        foreach ( $obj as $prop => &$value )
        {
            if ( isset( $data[$prop] ) )
                $value = $data[$prop];
        }
        return $this->join( $obj, $joinInfo );
    }

    /**
     * Joins in foreign objects ( one to many realtions )
     *
     * @param \ezp\Persistence\ValueObject $item
     * @param array $joinInfo See {@link find()}
     * @return ValueObject
     */
    private function join( ValueObject $item, array $joinInfo = array() )
    {
        foreach ( $joinInfo as $property => $info )
        {
            foreach ( $info['match'] as $key => $matchProperty )
                $info['match'][$key] = $item->$matchProperty;
            $item->$property = $this->find( $info['type'],
                                            $info['match'],
                                            ( isset( $info['sub'] ) ? $info['sub'] : array() )
            );
        }
        return $item;
    }
}
