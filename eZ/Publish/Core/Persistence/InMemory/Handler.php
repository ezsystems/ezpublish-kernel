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
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\Core\Repository\ValidatorService,
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
     *
     * @param \eZ\Publish\Core\Repository\ValidatorService $validatorService
     * @param \eZ\Publish\API\Repository\FieldTypeTools $fieldTypeTools
     */
    public function __construct( ValidatorService $validatorService, FieldTypeTools $fieldTypeTools )
    {
        $this->backend = new Backend( json_decode( file_get_contents( __DIR__ . '/data.json' ), true ), $validatorService, $fieldTypeTools );
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
        //@todo implement
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        //@todo implement
    }

    /**
     */
    public function beginTransaction()
    {
        //throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     */
    public function commit()
    {
        //throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     */
    public function rollback()
    {
        //throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Get/create instance of service handler objects
     *
     * @param string $className
     * @return object
     * @throws MissingClass
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
