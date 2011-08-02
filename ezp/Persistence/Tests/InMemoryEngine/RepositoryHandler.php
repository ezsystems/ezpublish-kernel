<?php
/**
 * File containing the RepositoryHandler in memory implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Persistence\Interfaces\RepositoryHandler as RepositoryHandlerInterface,
    ezp\Base\Exception\MissingClass;

/**
 * The main handler for in memory Storage Engine
 *
 */
class RepositoryHandler implements RepositoryHandlerInterface
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
     * @var ezp\Persistence\Tests\InMemoryEngine\Backend
     */
    protected $backend;

    /**
     * Setup instance with an instance of Backend class
     */
    public function __construct()
    {
        $this->backend = new Backend(
            array(
                "Content" => "ezp\\Persistence\\Content",
                "Content\\Field" => "ezp\\Persistence\\Content\\Field",
                "Content\\Location" => "ezp\\Persistence\\Content\\Location",
                "Content\\Version" => "ezp\\Persistence\\Content\\Version",
                "Content\\Section" => "ezp\\Persistence\\Content\\Section",
                "Content\\Type" => "ezp\\Persistence\\Content\\Type",
                "Content\\Type\\FieldDefintion" => "ezp\\Persistence\\Content\\Type\\FieldDefintion",
                "Content\\Type\\Group" => "ezp\\Persistence\\Content\\Type\\Group",
                "User" => "ezp\\Persistence\\User",
                "User\\Role" => "ezp\\Persistence\\User\\Role",
                "User\\Policy" => "ezp\\Persistence\\User\\Policy",
            )
        );
    }

    /**
     * @return ezp\Persistence\Content\Interfaces\ContentHandler
     */
    public function contentHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Tests\\InMemoryEngine\\ContentHandler' );
    }

    /**
     * @return ezp\Persistence\Content\Type\Interfaces\Handler
     */
    public function contentTypeHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Tests\\InMemoryEngine\\ContentTypeHandler' );
    }

    /**
     * @return ezp\Persistence\Content\Interfaces\LocationHandler
     */
    public function locationHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Tests\\InMemoryEngine\\LocationHandler' );
    }

    /**
     * @return ezp\Persistence\User\Interfaces\UserHandler
     */
    public function userHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Tests\\InMemoryEngine\\UserHandler' );
    }

    /**
     * @return ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function sectionHandler()
    {
        return $this->serviceHandler( 'ezp\\Persistence\\Tests\\InMemoryEngine\\SectionHandler' );
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

        throw new MissingClass( $className, 'service handler' );
    }
}
