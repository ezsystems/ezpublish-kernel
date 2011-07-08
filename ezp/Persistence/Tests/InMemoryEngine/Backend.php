<?php
/**
 * File containing the Backend for in-memory storage engine
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\Persistence\Tests\InMemoryEngine;

/**
 * The Storage Engine backend for in memory storage
 * Reads input from js files in provided directory and fills in memory db store.
 * The in memory db store and also json representation have a one to one mapping to defined value objects.
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
     * @param string $jsonDir
     */
    public function __construct( $jsonDir = 'ezp/Persistence/Tests/data' )
    {
        foreach ( glob( "$jsonDir/*.js" ) as $file )
        {
            $this->data[basename( $file, '.js' )] = json_decode( file_get_contents( $file ), true );
        }
    }

    /**
     * Creates data in in memory store
     *
     * @param string $module
     * @param string $type
     * @param int|string $id
     * @param array $data
     * @return object
     */
    public function create( $module, $type, array $data )
    {
        $data['id'] = count( $this->data[$module][$type] );
        $this->data[$module][$type][$data['id']] = $data;
        return $this->toValue( $module, $type, $data );
    }

    /**
     * Reads data from in memory store
     *
     * @param string $module
     * @param string $type
     * @param int|string $id
     * @return object|null
     */
    public function read( $module, $type, $id )
    {
        if ( isset( $this->data[$module][$type][$id] ) )
            return $this->toValue( $module, $type, $this->data[$module][$type][$id] );
        return null;
    }

    /**
     * Find all data from in memory store for a specific type
     *
     * @param string $module
     * @param string $type
     * @return object[]
     */
    public function find( $module, $type )
    {
        if ( isset( $this->data[$module][$type] ) )
        {
            $items = array();
            foreach ( $this->data[$module][$type] as $hash )
                $items[] = $this->toValue( $module, $type, $hash );
            return $items;
        }
        return array();
    }

    /**
     * Updates data in in memory store
     *
     * @param string $module
     * @param string $type
     * @param int|string $id
     * @param array $data
     * @return bool False if data does not exist and can not be updated
     */
    public function update( $module, $type, $id, array $data )
    {
        if ( !isset( $this->data[$module][$type][$id] ) )
            return false;

        $this->data[$module][$type][$id] = $data;
        return true;
    }

    /**
     * Deletes data in in memory store
     *
     * @param string $module
     * @param string $type
     * @param int|string $id
     * @return bool False if data does not exist and can not be deleted
     */
    public function delete( $module, $type, $id )
    {
        if ( !isset( $this->data[$module][$type][$id] ) )
            return false;

        unset( $this->data[$module][$type][$id] );
        return true;
    }

    /**
     * Creates Value object / struct based on array value from Backend.
     *
     * @param string $module
     * @param string $type
     * @param array $data
     * @return object
     */
    protected function toValue( $module, $type, array $data )
    {
        $className = "\\ezp\\Persistence\\$module\\$type";
        $obj = new $className;
        foreach( $obj as $prop => $value )
        {
            if ( isset( $data[$prop] ) )
                $obj->$prop = $data[$prop];
        }
        return $obj;
    }
}
