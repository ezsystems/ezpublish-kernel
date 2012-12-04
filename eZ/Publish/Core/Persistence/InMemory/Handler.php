<?php
/**
 * File containing the Handler in memory implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Handler as HandlerInterface,
    eZ\Publish\Core\Base\Exceptions\MissingClass;

/**
 * The main handler for in memory Storage Engine
 */
class Handler implements HandlerInterface
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
     * @var \eZ\Publish\Core\Persistence\InMemory\Backend
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
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\SearchHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentTypeHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\LanguageHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\LocationHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ObjectStateHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UserHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\SectionHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\TrashHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UrlAliasHandler' );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        return $this->serviceHandler( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UrlWildcardHandler' );
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->backend->beginTransaction();
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
        $this->backend->commit();
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
        $this->backend->rollback();
    }

    /**
     * Get/create instance of service handler objects
     *
     * @param string $className
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\MissingClass
     *
     * @return object
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
