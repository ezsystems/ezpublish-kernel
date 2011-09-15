<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\NotFound,
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
     * @param array $data
     * @param bool $autoIncrement
     * @return object
     * @throws InvalidArgumentValue On invalid $type
     * @throws Logic If $autoIncrement is false but $data does not include an id
     */
    public function create( $type, array $data, $autoIncrement = true )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        if ( $autoIncrement )
        {
            $data['id'] = $this->getNextId( $type );
        }
        else if ( !$data['id'] )
        {
          throw new Logic( 'create', '$autoIncrement is false but no id is provided' );
        }
        else
        {
            foreach ( $this->data[$type] as $item )
            {
                if ( $item['id'] == $data['id'] )
                    throw new Logic( 'create', 'provided id already exist' );
            }
        }

        $this->data[$type][] = $data;
        return $this->toValue( $type, $data );
    }

    /**
     * Reads data from in memory store
     *
     * @param string $type
     * @param int|string $id
     * @return object
     * @throws InvalidArgumentValue On invalid $type
     * @throws \ezp\Base\Exception\NotFound If data does not exist
     */
    public function load( $type, $id )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $return = null;
        $found = false;
        foreach ( $this->data[$type] as $item )
        {
            if ( $item['id'] != $id )
                continue;
            else if ( $return )
                throw new Logic( $type, "more than one item exist with id: {$id}" );

            $return = $this->toValue( $type, $item );
            $found = true;
        }

        if ( !$found )
            throw new NotFound( $type, $id );

        return $return;
    }

    /**
     * Find data from in memory store for a specific type that matches $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A multi level array with property => value to match against
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
     * @uses rawFind()
     */
    public function find( $type, array $match = array(), array $joinInfo = array() )
    {
        $items = $this->rawFind( $type, $match, $joinInfo );
        foreach ( $items as $key => $item )
            $items[$key] = $this->toValue( $type, $item, $joinInfo );

        return $items;
    }

    /**
     * Updates data in in memory store
     *
     * @param string $type
     * @param int|string $id
     * @param array $data
     * @param bool $union Specifies if data should be merged with existing data or not
     * @return bool False if data does not exist and can not be updated
     * @uses updateByMatch()
     */
    public function update( $type, $id, array $data, $union = true )
    {
        return $this->updateByMatch( $type, array( 'id' => $id ), $data, $union );
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
     * @param bool $union Specifies if data should be merged with existing data or not
     * @return bool False if data does not exist and can not be updated
     * @throws InvalidArgumentValue On invalid $type
     */
    public function updateByMatch( $type, array $match, array $data, $union = true )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        // Make sure id isn't changed
        unset( $data['id'] );

        $return = false;
        foreach ( $this->data[$type] as $key => $item )
        {
            if ( $this->match( $item, $match ) )
            {
                if ( $union )
                    $this->data[$type][$key] = $data + $this->data[$type][$key];
                else
                    $this->data[$type][$key] = $data;
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Deletes data in in memory store
     *
     * @param string $type
     * @param int|string $id
     * @throws \ezp\Base\Exception\NotFound If data does not exist
     * @uses deleteByMatch()
     */
    public function delete( $type, $id )
    {
        $this->deleteByMatch( $type, array( 'id' => $id ) );
    }

    /**
     * Deletes data in in memory store
     *
     * Useful in cases where a specific state of an object should be updated,
     * Type with version=0 for instance.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @throws InvalidArgumentValue On invalid $type
     * @throws \ezp\Base\Exception\NotFound If no data to delete have been found
     */
    public function deleteByMatch( $type, array $match )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $found = false;
        foreach ( $this->data[$type] as $key => $item )
        {
            if ( $this->match( $item, $match ) )
            {
                unset( $this->data[$type][$key] );
                $found = true;
            }
        }

        if ( !$found )
            throw new NotFound( $type, $match );
    }

    /**
     * Find count of objects of a given type matching a simple $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     * @param array $joinInfo See {@link find()}
     * @return int
     * @uses rawFind()
     */
    public function count( $type, array $match = array(), array $joinInfo = array() )
    {
        return count( $this->rawFind( $type, $match, $joinInfo ) );
    }

    /**
     * Find data from in memory store for a specific type that matches $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A multi level array with property => value to match against
     * @param array $joinInfo See {@link find()}
     * @return array[]
     * @throws InvalidArgumentValue On invalid $type
     * @throws Logic When there is a collision between match rules in $joinInfo and $match
     */
    protected function rawFind( $type, array $match = array(), array $joinInfo = array() )
    {
        if ( !is_scalar($type) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $items = array();
        foreach ( $this->data[$type] as $item )
        {
            foreach ( $joinInfo as $joinProperty => $joinItem )
            {
                foreach ( $joinItem['match'] as $joinMatchKey => $joinMatchProperty )
                {
                    $joinItem['match'][$joinMatchKey] = $item[$joinMatchProperty];
                    if ( isset( $match[$joinProperty][$joinMatchKey] ) )
                        throw new Logic( "\$match[$joinProperty][$joinMatchKey]", "collision with match in \$joinInfo" );
                }
                $item[$joinProperty] = $this->rawFind( $joinItem['type'],
                                                       $joinItem['match'],
                                                       ( isset( $joinItem['sub'] ) ? $joinItem['sub'] : array() ) );
            }
            if ( $this->match( $item, $match ) )
                $items[] = $item;
        }
        return $items;
    }

    /**
     * Checks if a $item (a raw VO item) matches $match recursively
     *
     * @param array $item
     * @param array $match
     * @return bool
     */
    private function match( array $item, array $match )
    {
        foreach ( $match as $matchProperty => $matchValue )
        {
            if ( !isset( $item[$matchProperty] ) )
                return false;

            if ( is_array( $item[$matchProperty] ) )
            {
                // sub match. When $matchValue is array, assume it's a joined
                // list of value objects and look if one of them matches
                if ( is_array( $matchValue ) )
                {
                    $hasSubMatch = false;
                    foreach ( $item[$matchProperty] as $subItem )
                    {
                        if ( $this->match( $subItem, $matchValue ) )
                            $hasSubMatch = true;
                    }
                    if ( !$hasSubMatch )
                        return false;
                }
                // otherwise check if match value is part of array
                else if ( !in_array( $matchValue, $item[$matchProperty] ) )
                {
                    return false;
                }
            }
            // A property trying to match a list of values
            // Like an SQL IN() statement
            else if ( is_array( $matchValue ) )
            {
                if ( !in_array( $item[$matchProperty], $matchValue ) )
                    return false;
            }
            // Use of wildcards like in SQL, at the end of $matchValue
            // i.e. /1/2/% (for pathString)
            else if ( ( $wildcardPos = strpos( $matchValue, '%' ) ) > 0 && ( $wildcardPos === strlen( $matchValue ) - 1 ) )
            {
                // Returns true if $item[$matchProperty] begins with $matchValue (minus '%' wildcard char)
                $matchValue = substr( $matchValue, 0, -1 );
                $pos = strpos( $item[$matchProperty], $matchValue );
                if ( $matchValue === $item[$matchProperty] )
                    return false;
                if ( $pos !== 0 )
                    return false;
            }
            // plain equal match
            else if ( $item[$matchProperty] != $matchValue )
            {
                return false;
            }
        }

        return true;
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
        foreach ( $this->data[$type] as $item )
        {
            $id = max( $id, $item['id'] );
        }
        return $id + 1;
    }

    /**
     * Creates Value object based on array value from Backend.
     *
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
        return $this->joinToValue( $obj, $joinInfo );
    }

    /**
     * Creates value objects on join properties
     *
     * @param \ezp\Persistence\ValueObject $item
     * @param array $joinInfo See {@link find()}
     * @return ValueObject
     */
    private function joinToValue( ValueObject $item, array $joinInfo = array() )
    {
        foreach ( $joinInfo as $property => $info )
        {
            if ( isset( $info['single'] ) && $info['single'] )
            {
                $value =& $item->$property;
                $value = $this->toValue( $info['type'],
                                        $value[0],
                                        ( isset( $info['sub'] ) ? $info['sub'] : array() ) );
                continue;
            }

            foreach ( $item->$property as &$joinItem )
            {
                $joinItem = $this->toValue( $info['type'],
                                        $joinItem,
                                        ( isset( $info['sub'] ) ? $info['sub'] : array() ) );

            }
        }
        return $item;
    }
}
