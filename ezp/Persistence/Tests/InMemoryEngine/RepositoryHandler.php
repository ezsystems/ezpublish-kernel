<?php
/**
 * File containing the RepositoryHandler in memory implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\Persistence\Tests\InMemoryEngine;

/**
 * The main handler for in memory Storage Engine
 *
 * @package ezp
 * @subpackage persistence
 */
class RepositoryHandler implements \ezp\Persistence\Interfaces\RepositoryHandler
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
     * @var \ezp\Persistence\Tests\InMemoryEngine\Backend
     */
    protected $backend;

    /**
     * Setup instance with an instance of Backend class
     */
    public function __construct()
    {
        $this->backend = new Backend();
    }
    /**
     * @return \ezp\Persistence\Content\Interfaces\ContentHandler
     */
    public function contentHandler()
    {
        return $this->serviceHandler( '\ezp\Persistence\Tests\InMemoryEngine\ContentHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Type\Interfaces\Handler
     */
    public function contentTypeHandler()
    {
        return $this->serviceHandler( '\ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Interfaces\LocationHandler
     */
    public function locationHandler()
    {
        return $this->serviceHandler( '\ezp\Persistence\Tests\InMemoryEngine\LocationHandler' );
    }

    /**
     * @return \ezp\Persistence\User\Interfaces\UserHandler
     */
    public function userHandler()
    {
        return $this->serviceHandler( '\ezp\Persistence\Tests\InMemoryEngine\UserHandler' );
    }

    /**
     * @return \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function sectionHandler()
    {
        return $this->serviceHandler( '\ezp\Persistence\Tests\InMemoryEngine\SectionHandler' );
    }

    /**
     */
    public function beginTransaction()
    {
    }

    /**
     */
    public function commit()
    {
    }

    /**
     */
    public function rollback()
    {
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

        throw new \RuntimeException( "Could not load '$className' handler!" );
    }
}
