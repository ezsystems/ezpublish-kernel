<?php
/**
 * File containing ObjectStorage class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Object storage
 *
 * A pr request object storage which only keeps X number of objects in
 * cache at a time to avoid exces memory usage. Since it is pr type, and
 * on intention simple with no knowledge of the objects them self it
 * is not recommended to store anything but the root aggregate to avoid
 * having to discard lots of different ca caches and do logic to use
 * correct. Also, this is not meant to cache all the api's, only the hotspot
 *
 */
class ObjectStorage
{
    /**
     * The limit pr class type
     */
    const OBJECT_LIMIT_PR_TYPE = 10;

    /**
     * The identity map that holds references to all managed entities.
     *
     * The entities are grouped by their class name and then ->id property.
     *
     * Structure:
     *  array(
     *      'eZ\Publish\Core\Repository\Values\Content\Content' => array(
     *          '1' => ValueObject
     *      )
     *  )
     *
     * @var array[]
     */
    private $identityMap = array();

    /**
     * Map of all identifiers of managed entities
     *
     * Keys are object hash (spl_object_hash) and value is primary key string {@see $identityMap}
     * As code that uses this have an instance of the object, class name can be retrieved from it.
     *
     * Structure:
     *  array(
     *      '<object_hash>' => '1'
     *  )
     *
     * @var int[]
     */
    private $entityIdentifiers = array();

    /**
     * @var array Hash with allowed classes and with short identifier as key, eg: content
     */
    private $identifierFQNMap = array();

    /**
     * @param array $identifierFQNMap
     */
    public function __construct(
        array $identifierFQNMap = array(
            'content' => 'eZ\Publish\Core\Repository\Values\Content\Content',
            'section' => 'eZ\Publish\Core\Repository\Values\Content\Section',
        )
    )
    {
        $this->identifierFQNMap = $identifierFQNMap;
    }

    /**
     * Attach a value object
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     *
     * @throws \RuntimeException On issues where object hash does not exist, but class+id does
     * @throws \InvalidArgumentException On invalid $object class as defined in $identifierFQNMap
     * @return bool False if object already was part of storage
     */
    public function add( ValueObject $object )
    {
        $hash = spl_object_hash( $object );
        if ( isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        if ( !in_array( $className, $this->identifierFQNMap ) )
            throw new \InvalidArgumentException( "Object of type {$className} is not supported as it is not defined in \$this->identifierFQNMap" );

        $id = $object->id;
        if ( isset( $this->identityMap[$className][$id] ) )
            throw new \RuntimeException( "Object of type {$className} & with id {$id} seems to exist even though object hash is different" );

        if (
            !empty( $this->identityMap[$className] ) &&
            count( $this->identityMap[$className] ) > self::OBJECT_LIMIT_PR_TYPE
        )
        {
            $this->identityMap[$className] = array_slice(
                $this->identityMap[$className],
                ((int) self::OBJECT_LIMIT_PR_TYPE * 0.3 ),// Remove 30% to avoid having to remove on each add()
                null,
                true
            );
        }
        $this->identityMap[$className][$id] = $object;
        $this->entityIdentifiers[$hash] = $id;
        return true;
    }

    /**
     * Checks if storage contains a value object
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     *
     * @return bool
     */
    public function has( ValueObject $object )
    {
        $hash = spl_object_hash( $object );
        return isset( $this->entityIdentifiers[$hash] );
    }

    /**
     * Remove a value object
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     *
     * @throws \RuntimeException On issues where object hash does not match the object stored
     * @return bool
     */
    public function remove( ValueObject $object )
    {
        $hash = spl_object_hash( $object );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            return false;

        $className = get_class( $object );
        $id = $this->entityIdentifiers[$hash];
        unset( $this->entityIdentifiers[$hash] );

        if ( !isset( $this->identityMap[$className][$id] ) )
            throw new \RuntimeException( "Object of type {$className} & with id {$id} could not be found even if object hash existed" );

        unset( $this->identityMap[$className][$id] );
        return true;
    }

    /**
     * Remove a value object by class name and primary id
     *
     * @param string $identifier
     * @param int $id
     *
     * @throws \RuntimeException On issues where object hash does not match the object stored
     * @throws \InvalidArgumentException On invalid $identifier
     * @return bool
     */
    public function discard( $identifier, $id )
    {
        if ( !isset( $this->identifierFQNMap[$identifier] ) )
            throw new \InvalidArgumentException( "Identifier '{$identifier}' does not exist in \$this->identifierFQNMap" );

        $className = $this->identifierFQNMap[$identifier];
        if ( !isset( $this->identityMap[$className][$id] ) )
            return false;

        $hash = spl_object_hash( $this->identityMap[$className][$id] );
        if ( !isset( $this->entityIdentifiers[$hash] ) )
            throw new \RuntimeException( "Object of type {$className} & with id {$id} was found but object hash differs" );

        unset( $this->identityMap[$className][$id] );
        unset( $this->entityIdentifiers[$hash] );
        return true;
    }

    /**
     * Get a Value object by class name and primary id
     *
     * @param $identifier
     * @param int $id
     *
     * @throws \InvalidArgumentException On invalid $identifier
     * @return \eZ\Publish\API\Repository\Values\ValueObject|null
     */
    public function get( $identifier, $id )
    {
        if ( !isset( $this->identifierFQNMap[$identifier] ) )
            throw new \InvalidArgumentException( "Identifier '{$identifier}' does not exist in \$this->identifierFQNMap" );

        $className = $this->identifierFQNMap[$identifier];
        if ( !isset( $this->identityMap[$className][$id] ) )
            return null;
        return $this->identityMap[$className][$id];
    }

    /**
     * Presence of a Value object by class name and primary id
     *
     * @param string $identifier
     * @param int $id
     *
     * @throws \InvalidArgumentException On invalid $identifier
     * @return bool
     */
    public function exists( $identifier, $id )
    {
        if ( !isset( $this->identifierFQNMap[$identifier] ) )
            throw new \InvalidArgumentException( "Identifier '{$identifier}' does not exist in \$this->identifierFQNMap" );

        return isset( $this->identityMap[$this->identifierFQNMap[$identifier]][$id] );
    }

    /**
     * Reset storage (empty it)
     */
    public function reset()
    {
        $this->identityMap = array();
        $this->entityIdentifiers = array();
    }
}