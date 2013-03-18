<?php
/**
 * File containing the Persistence Cache Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandlerInterface;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\SearchHandler as CacheSearchHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * Persistence Cache Handler class
 */
class Handler implements PersistenceHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var SectionHandler
     */
    protected $sectionHandler;

    /**
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * @var ContentLanguageHandler
     */
    protected $contentLanguageHandler;

    /**
     * @var ContentTypeHandler
     */
    protected $contentTypeHandler;

    /**
     * @var LocationHandler
     */
    protected $locationHandler;

    /**
     * @var UserHandler
     */
    protected $userHandler;

    /**
     * @var SearchHandler
     */
    protected $searchHandler;

    /**
     * @var UrlAliasHandler
     */
    protected $urlAliasHandler;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * Construct the class
     *
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory Must be factory for inner persistence, ie: legacy
     * @param SectionHandler $sectionHandler
     * @param LocationHandler $locationHandler
     * @param ContentHandler $contentHandler
     * @param ContentLanguageHandler $contentLanguageHandler
     * @param ContentTypeHandler $contentTypeHandler
     * @param UserHandler $userHandler
     * @param SearchHandler $searchHandler
     * @param UrlAliasHandler $urlAliasHandler
     * @param PersistenceLogger $logger
     */
    public function __construct(
        PersistenceFactory $persistenceFactory,
        CacheSectionHandler $sectionHandler,
        CacheLocationHandler $locationHandler,
        CacheContentHandler $contentHandler,
        CacheContentLanguageHandler $contentLanguageHandler,
        CacheContentTypeHandler $contentTypeHandler,
        CacheUserHandler $userHandler,
        CacheSearchHandler $searchHandler,
        CacheUrlAliasHandler $urlAliasHandler,
        PersistenceLogger $logger
    )
    {
        $this->persistenceFactory = $persistenceFactory;
        $this->sectionHandler = $sectionHandler;
        $this->locationHandler = $locationHandler;
        $this->contentHandler = $contentHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->userHandler = $userHandler;
        $this->searchHandler = $searchHandler;
        $this->urlAliasHandler = $urlAliasHandler;
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        return $this->contentHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        return $this->searchHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        return $this->contentTypeHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        return $this->contentLanguageHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        return $this->locationHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler()
    {
        $this->logger->logUnCachedHandler( __METHOD__ );
        return $this->persistenceFactory->getObjectStateHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler()
    {
        return $this->userHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        return $this->sectionHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        $this->logger->logUnCachedHandler( __METHOD__ );
        return $this->persistenceFactory->getTrashHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler()
    {
        return $this->urlAliasHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        $this->logger->logUnCachedHandler( __METHOD__ );
        return $this->persistenceFactory->getUrlWildcardHandler();
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->logger->logCall( __METHOD__ );
        $this->persistenceFactory->getPersistenceHandler()->beginTransaction();
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
        $this->logger->logCall( __METHOD__ );
        $this->persistenceFactory->getPersistenceHandler()->commit();
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
        $this->logger->logCall( __METHOD__ );
        $this->persistenceFactory->getPersistenceHandler()->rollback();
    }
}
