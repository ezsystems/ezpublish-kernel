<?php
/**
 * File containing the Persistence Cache Handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandlerInterface;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\LocationSearchHandler as CacheLocationSearchHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\SearchHandler as CacheSearchHandler;
use eZ\Publish\Core\Persistence\Cache\TrashHandler as CacheTrashHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;

/**
 * Persistence Cache Handler class
 */
class Handler implements PersistenceHandlerInterface
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

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
     * @var LocationSearchHandler
     */
    protected $locationSearchHandler;

    /**
     * @var UserHandler
     */
    protected $userHandler;

    /**
     * @var SearchHandler
     */
    protected $searchHandler;

    /**
     * @var TrashHandler
     */
    protected $trashHandler;

    /**
     * @var UrlAliasHandler
     */
    protected $urlAliasHandler;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    protected $cache;

    /**
     * Construct the class
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler Must be factory for inner persistence, ie: legacy
     * @param \eZ\Publish\Core\Persistence\Cache\SectionHandler $sectionHandler
     * @param \eZ\Publish\Core\Persistence\Cache\LocationHandler $locationHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentHandler $contentHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler $contentLanguageHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler $contentTypeHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UserHandler $userHandler
     * @param \eZ\Publish\Core\Persistence\Cache\SearchHandler $searchHandler
     * @param \eZ\Publish\Core\Persistence\Cache\TrashHandler $trashHandler
     * @param \eZ\Publish\Core\Persistence\Cache\LocationSearchHandler $locationSearchHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler $urlAliasHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator $cache
     */
    public function __construct(
        PersistenceHandlerInterface $persistenceHandler,
        CacheSectionHandler $sectionHandler,
        CacheLocationHandler $locationHandler,
        CacheContentHandler $contentHandler,
        CacheContentLanguageHandler $contentLanguageHandler,
        CacheContentTypeHandler $contentTypeHandler,
        CacheUserHandler $userHandler,
        CacheSearchHandler $searchHandler,
        CacheTrashHandler $trashHandler,
        CacheLocationSearchHandler $locationSearchHandler,
        CacheUrlAliasHandler $urlAliasHandler,
        PersistenceLogger $logger,
        CacheServiceDecorator $cache
    )
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->sectionHandler = $sectionHandler;
        $this->locationHandler = $locationHandler;
        $this->contentHandler = $contentHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->userHandler = $userHandler;
        $this->searchHandler = $searchHandler;
        $this->trashHandler = $trashHandler;
        $this->locationSearchHandler = $locationSearchHandler;
        $this->urlAliasHandler = $urlAliasHandler;
        $this->logger = $logger;
        $this->cache = $cache;
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
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationSearchHandler()
    {
        return $this->locationSearchHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler()
    {
        $this->logger->logUnCachedHandler( __METHOD__ );
        return $this->persistenceHandler->objectStateHandler();
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
        return $this->trashHandler;
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
        return $this->persistenceHandler->urlWildcardHandler();
    }

    /**
     * Begin transaction
     *
     * @todo Either disable cache or layer it with in-memory cache per transaction (last layer would be the normal layer)
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->logger->logCall( __METHOD__ );
        $this->persistenceHandler->beginTransaction();
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
        $this->persistenceHandler->commit();
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
        $this->cache->clear();
        $this->persistenceHandler->rollback();
    }
}
