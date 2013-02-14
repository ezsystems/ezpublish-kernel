<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\ValueObject;
use LogicException;

/**
 * The Storage Engine backend for in memory storage
 * Reads input from js files in provided directory and fills in memory db store.
 *
 * The in memory db store and also json representation have a one to one mapping to defined value objects.
 * But only their plain properties, associations are not handled and all data is stored in separate "buckets" similar
 * to how it would be in a RDBMS servers.
 */
class Backend
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * Stack of data for transactions
     *
     * Current data is always kept in $data, this is for rollbacks and transaction count.
     *
     * @var array
     */
    protected $transactionDataStack = array();

    /**
     * Construct backend and assign data
     *
     * Use:
     *     new Backend( json_decode( file_get_contents( __DIR__ . '/data.json' ), true ) );
     *
     * @param array $data Data where key is type like "Content" or "Content\\Type" which then have to map to
     *                    Value objects in eZ\Publish\SPI\Persistence\*, data is an array of hash values with same structure as
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
     * @param boolean $autoIncrement
     * @param string $idColumn By default, id column is 'id', but this can be customized here (e.g. for 'contentId')
     *
     * @return object
     * @throws InvalidArgumentValue On invalid $type
     * @throws LogicException If $autoIncrement is false but $data does not include an id
     * @throws LogicException If provided id already exists (and if defined, data contain same status property value)
     */
    public function create( $type, array $data, $autoIncrement = true, $idColumn = 'id' )
    {
        if ( !is_scalar( $type ) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        if ( $autoIncrement )
        {
            $data[$idColumn] = $this->getNextId( $type, $idColumn );
        }
        else if ( !$data[$idColumn] )
        {
            throw new LogicException( '\'create\' logic error, $autoIncrement is false but no id is provided' );
        }

        foreach ( $this->data[$type] as $item )
        {
            if (
                // Same identifier
                $item[$idColumn] == $data[$idColumn] &&
                (
                    // and "status" matches
                    ( isset( $item['status'] ) && $item['status'] == $data['status'] ) ||
                    // or "_status" matches
                    ( isset( $item['_status'] ) && $item['_status'] == $data['_status'] ) ||
                    // or no status available
                    !( isset( $item['status'] ) || isset( $item['_status'] ) )
                )
            )
                throw new LogicException( "'create' logic error, provided id already exist" );
        }

        /*foreach ( $data as $prop => $value )
        {
            if ( $value === null )
                throw new InvalidArgumentValue( 'data', "'$prop' on '$type' was of value NULL" );
        }*/

        $this->data[$type][] = $data;
        return $this->toValue( $type, $data );
    }

    /**
     * Reads data from in memory store
     *
     * @param string $type
     * @param int|string $id
     * @param string $idColumn
     *
     * @return object
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue On invalid $type
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If data does not exist
     * @throws LogicException If several items exists with same id
     */
    public function load( $type, $id, $idColumn = 'id' )
    {
        if ( !is_scalar( $type ) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        $return = null;
        $found = false;
        foreach ( $this->data[$type] as $item )
        {
            if ( $item[$idColumn] != $id )
                continue;
            if ( $return )
                throw new LogicException( "Logic error, more than one item exist with id: {$id}" );

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
     * @uses rawFind()
     *
     * @return object[]
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
     * @param boolean $union Specifies if data should be merged with existing data or not
     *
     * @uses updateByMatch()
     *
     * @return boolean False if data does not exist and can not be updated
     */
    public function update( $type, $id, array $data, $union = true, $idColumn = 'id' )
    {
        return $this->updateByMatch( $type, array( $idColumn => $id ), $data, $union, $idColumn );
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
     * @param boolean $union Specifies if data should be merged with existing data or not
     *
     * @throws InvalidArgumentValue On invalid $type
     *
     * @return boolean False if data does not exist and can not be updated
     */
    public function updateByMatch( $type, array $match, array $data, $union = true, $idColumn = 'id' )
    {
        if ( !is_scalar( $type ) || !isset( $this->data[$type] ) )
            throw new InvalidArgumentValue( 'type', $type );

        // Make sure id isn't changed
        unset( $data[$idColumn] );

        /*foreach ( $data as $prop => $value )
        {
            if ( $value === null )
                throw new InvalidArgumentValue( 'data', "'$prop' on '$type' was of value NULL" );
        }*/

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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If data does not exist
     * @uses deleteByMatch()
     */
    public function delete( $type, $id, $idColumn = 'id' )
    {
        $this->deleteByMatch( $type, array( $idColumn => $id ) );
    }

    /**
     * Deletes data in in memory store
     *
     * Useful in cases where a specific state of an object should be updated,
     * Type with version=0 for instance.
     *
     * @param string $type
     * @param array $match A flat array with property => value to match against
     *
     * @throws InvalidArgumentValue On invalid $type
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no data to delete have been found
     */
    public function deleteByMatch( $type, array $match )
    {
        if ( !is_scalar( $type ) || !isset( $this->data[$type] ) )
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
     *
     * @uses rawFind()
     *
     * @return int
     */
    public function count( $type, array $match = array(), array $joinInfo = array() )
    {
        return count( $this->rawFind( $type, $match, $joinInfo ) );
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        // copy current data to transaction stack
        $this->transactionDataStack[] = $this->data;
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit()
    {
        if ( empty( $this->transactionDataStack ) )
            throw new \RuntimeException( "No transactions in progress" );

        // remove one level of data from transaction stack
        array_pop( $this->transactionDataStack );
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        if ( empty( $this->transactionDataStack ) )
            throw new \RuntimeException( "No transactions in progress" );

        // pop last data set from transaction stack back to live $data set to wipe out changes
        $this->data = array_pop( $this->transactionDataStack );
    }

    /**
     * Find data from in memory store for a specific type that matches $match (empty array will match all)
     *
     * Note does not support joins, so only properties on $type is matched.
     *
     * @param string $type
     * @param array $match A multi level array with property => value to match against
     * @param array $joinInfo See {@link find()}
     *
     * @return array[]
     * @throws InvalidArgumentValue On invalid $type
     * @throws LogicException When there is a collision between match rules in $joinInfo and $match
     */
    protected function rawFind( $type, array $match = array(), array $joinInfo = array() )
    {
        if ( !is_scalar( $type ) || !isset( $this->data[$type] ) )
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
                        throw new LogicException( "\$match[$joinProperty][$joinMatchKey] logic error, collision with match in \$joinInfo" );
                }
                $item[$joinProperty] = $this->rawFind(
                    $joinItem['type'],
                    $joinItem['match'],
                    ( isset( $joinItem['sub'] ) ? $joinItem['sub'] : array() )
                );
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
     *
     * @return boolean
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
            // Use of wildcards like in SQL, at the start and/or end of $matchValue
            // i.e. %/5/% (for pathString)
            else if ( $ends = substr( $matchValue, -1 ) === "%" || substr( $matchValue, 0, 1 ) === "%" )
            {
                $starts = substr( $matchValue, 0, 1 ) === "%";
                if ( $starts ) $matchValue = substr( $matchValue, 1 );
                if ( $ends ) $matchValue = substr( $matchValue, 0, -1 );

                if ( $matchValue === $item[$matchProperty] )
                    return false;

                $pos = strpos( $item[$matchProperty], $matchValue );

                if ( $pos === false )
                    return false;

                if ( !$starts && $pos !== 0 )
                    return false;

                if ( !$ends && $pos !== ( strlen( $item[$matchProperty] ) - strlen( $matchValue ) ) )
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
     * @param string $type
     *
     * @return int
     */
    private function getNextId( $type, $idColumn = 'id' )
    {
        $id = 0;
        foreach ( $this->data[$type] as $item )
        {
            $id = max( $id, $item[$idColumn] );
        }
        return $id + 1;
    }

    /**
     * Creates Value object based on array value from Backend.
     *
     * @param string $type
     * @param array $data
     * @param array $joinInfo See {@link find()}
     *
     * @return object
     */
    protected function toValue( $type, array $data, array $joinInfo = array() )
    {
        $className = "eZ\\Publish\\SPI\\Persistence\\$type";
        $obj = new $className;
        foreach ( $obj as $prop => &$value )
        {
            if ( isset( $data[$prop] ) )
            {
                if ( $type === "Content\\Field" && $prop === "value" && ! $data["value"] instanceof FieldValue )
                {
                    $fieldTypeNS = $this->getFieldTypeNamespace( $obj );
                    $fieldValueClassName = "$fieldTypeNS\\Value";
                    $fieldTypeValue = new $fieldValueClassName;
                    foreach ( $data["value"] as $fieldValuePropertyName => $fieldValuePropertyValue )
                    {
                        $fieldTypeValue->$fieldValuePropertyName = $fieldValuePropertyValue;
                    }

                    $fieldTypeClassName = "$fieldTypeNS\\Type";
                    /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
                    $fieldType = new $fieldTypeClassName();
                    $value = $fieldType->toPersistenceValue( $fieldTypeValue );
                }
                else if ( $type === "Content\\Type\\FieldDefinition" && $prop === "fieldTypeConstraints" && !$data["fieldTypeConstraints"] instanceof FieldTypeConstraints )
                {
                    $value = new FieldTypeConstraints;
                    foreach ( $data["fieldTypeConstraints"] as $constraintName => $constraintValue )
                    {
                        $value->$constraintName = $constraintValue;
                    }
                }
                else if ( $type === "Content\\Type\\FieldDefinition" && $prop === "defaultValue" && !$data["defaultValue"] instanceof FieldValue )
                {
                    $value = new FieldValue;
                    foreach ( $data["defaultValue"] as $propertyName => $propertyValue )
                    {
                        $value->$propertyName = $propertyValue;
                    }
                }
                else if ( $type === "Content\\UrlAlias" && $prop === "id" )
                {
                    // id should be <parent>-<hash>, but as there is no property for link in VO and we need it in handler,
                    // returning everything here
                    // Note: before returning in handler id must be overwritten with <parent>-<hash>
                    $value = array(
                        "id" => $data["id"],
                        "parent" => $data["parent"],
                        "link" => $data["link"],
                    );
                }
                else
                {
                    $value = $data[$prop];
                }
            }
        }

        try
        {
            return $this->joinToValue( $obj, $joinInfo );
        }
        catch ( \Exception $e )
        {
            throw new \RuntimeException( "Could not join value using: " . var_export( $data, true ), 0, $e );
        }
    }

    /**
     * Creates value objects on join properties
     *
     * @param \eZ\Publish\SPI\Persistence\ValueObject $item
     * @param array $joinInfo See {@link find()}
     *
     * @return ValueObject
     */
    private function joinToValue( ValueObject $item, array $joinInfo = array() )
    {
        foreach ( $joinInfo as $property => $info )
        {
            if ( isset( $info['skip'] ) && $info['skip'] )
                continue;

            if ( isset( $info['single'] ) && $info['single'] )
            {
                $value =& $item->$property;
                if ( !empty( $value ) )
                {
                    $value = $this->toValue(
                        $info['type'],
                        $value[0],
                        ( isset( $info['sub'] ) ? $info['sub'] : array() )
                    );
                }
                else
                {
                    throw new \RuntimeException( "$property is supposed to be single(1), found none!" );
                    $value = null;
                }
                continue;
            }

            foreach ( $item->$property as &$joinItem )
            {
                $joinItem = $this->toValue(
                    $info['type'],
                    $joinItem,
                    ( isset( $info['sub'] ) ? $info['sub'] : array() )
                );
            }
        }
        return $item;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\ValueObject $obj
     *
     * @return string
     */
    protected function getFieldTypeNamespace( $obj )
    {
        if ( isset( $this->tempFieldTypeMapping[ $obj->type ] ) )
            return $this->tempFieldTypeMapping[ $obj->type ];

        throw new \Exception( "Following FieldType is not supported by InMemory storage {$obj->type}" );
    }

    /**
     * @var array
     */
    private $tempFieldTypeMapping = array(
        'ezstring' => 'eZ\\Publish\\Core\\FieldType\\TextLine',
        'ezinteger' => 'eZ\\Publish\\Core\\FieldType\\Integer',
        'ezauthor' => 'eZ\\Publish\\Core\\FieldType\\Author',
        'ezfloat' => 'eZ\\Publish\\Core\\FieldType\\Float',
        'eztext' => 'eZ\\Publish\\Core\\FieldType\\TextBlock',
        'ezboolean' => 'eZ\\Publish\\Core\\FieldType\\Checkbox',
        'ezdatetime' => 'eZ\\Publish\\Core\\FieldType\\DateAndTime',
        'ezkeyword' => 'eZ\\Publish\\Core\\FieldType\\Keyword',
        'ezurl' => 'eZ\\Publish\\Core\\FieldType\\Url',
        'ezcountry' => 'eZ\\Publish\\Core\\FieldType\\Country',
        'ezbinaryfile' => 'eZ\\Publish\\Core\\FieldType\\BinaryFile',
        'ezmedia' => 'eZ\\Publish\\Core\\FieldType\\Media',
        'ezxmltext' => 'eZ\\Publish\\Core\\FieldType\\XmlText',
        'ezobjectrelationlist' => 'eZ\\Publish\\Core\\FieldType\\RelationList',
        'ezselection' => 'eZ\\Publish\\Core\\FieldType\\Selection',
        'ezsrrating' => 'eZ\\Publish\\Core\\FieldType\\Rating',
        'ezimage' => 'eZ\\Publish\\Core\\FieldType\\Image',
        'ezobjectrelation' => 'eZ\\Publish\\Core\\FieldType\\Relation',
    );
}
