<?php
/**
 * File containing the RepositoryHandler in memory implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Repository\Handler as BaseRepositoryHandler,
    ezp\Base\Exception\MissingClass,
    RuntimeException;

/**
 * The main handler for in memory Storage Engine
 *
 */
class RepositoryHandler implements BaseRepositoryHandler
{
    /**
     * Instances of handlers
     *
     * @var object[]
     */
    protected $serviceHandlers = array();

    /**
     * Instance of in-memory backend that reads data from js files into memory and writes to memory
     *
     * @var \ezp\Persistence\Storage\InMemory\Backend
     */
    protected $backend;

    /**
     * Setup instance with an instance of Backend class
     */
    public function __construct()
    {
        $this->backend = new Backend( json_decode( file_get_contents( __DIR__ . '/data.json' ), true ) );
    }

    /**
     * @return \ezp\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\ContentHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\SearchHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\ContentTypeHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\LanguageHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\LocationHandler' );
    }

    /**
     * @return \ezp\Persistence\User\Handler
     */
    public function userHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\UserHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\SectionHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Storage\\InMemory\\TrashHandler' );
    }

    /**
     */
    public function beginTransaction()
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     */
    public function commit()
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     */
    public function rollback()
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

     /**
     * Get/create instance of service handler objects
     *
     * @param string $className
     * @return object
     * @throws RuntimeException
     */
    protected function serviceHandler( $className )
    {
        if ( isset( $this->serviceHandlers[$className] ) )
            return $this->serviceHandlers[$className];

        if ( class_exists( $className ) )
            return $this->serviceHandlers[$className] = new $className( $this, $this->backend );

        throw new MissingClass( $className, 'service handler' );
    }
}
