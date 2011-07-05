<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\persistence\tests\in_memory_engine;

/**
 * The main handler for Storage Engine
 *
 * @package ezp
 * @subpackage persistence
 */
class RepositoryHandler implements \ezp\persistence\RepositoryHandlerInterface
{
    /**
     * Instances of handlers
     *
     * @var \ezp\persistence\ServiceHandlerInterface[]
     */
    protected $serviceHandlers = array();

    /**
     * Instance of in-memory backend that reads data from js files into memory and writes to memory
     *
     * @var object
     */
    protected $backend;

	/**
	 * @return \ezp\persistence\content\ContentHandlerInterface
	 */
	public function contentHandler()
    {
        return $this->serviceHandler( '\ezp\persistence\tests\in_memory_engine\ContentHandler' );
    }

	/**
	 * @return \ezp\persistence\content_types\ContentTypeHandlerInterface
	 */
	public function contentTypeHandler()
    {
        return $this->serviceHandler( '\ezp\persistence\tests\in_memory_engine\ContentTypeHandler' );
    }

	/**
	 * @return \ezp\persistence\content\LocationHandlerInterface
	 */
	public function locationHandler()
    {
        return $this->serviceHandler( '\ezp\persistence\tests\in_memory_engine\LocationHandler' );
    }

	/**
	 * @return \ezp\persistence\user\UserHandlerInterface
	 */
	public function userHandler()
    {
        return $this->serviceHandler( '\ezp\persistence\tests\in_memory_engine\UserHandler' );
    }

	/**
	 * @return \ezp\persistence\content\SectionHandlerInterface
	 */
	public function sectionHandler()
    {
        return $this->serviceHandler( '\ezp\persistence\tests\in_memory_engine\SectionHandler' );
    }

	/**
	 */
	public function beginTransaction(){}

	/**
	 */
	public function commit(){}

	/**
	 */
	public function rollback(){}

     /**
     * Get/create instance of service handler objects
     *
     * @param string $className
     * @return \ezp\persistence\ServiceHandlerInterface
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
