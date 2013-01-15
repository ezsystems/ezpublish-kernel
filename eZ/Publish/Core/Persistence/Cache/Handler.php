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
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
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
     * @var ContentTypeHandler
     */
    protected $contentTypeHandler;

    /**
     * @var LocationHandler
     */
    protected $locationHandler;

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
     * @param ContentTypeHandler $contentTypeHandler
     * @param PersistenceLogger $logger
     */
    public function __construct(
        PersistenceFactory $persistenceFactory,
        CacheSectionHandler $sectionHandler,
        CacheLocationHandler $locationHandler,
        CacheContentTypeHandler $contentTypeHandler,
        PersistenceLogger $logger
    )
    {
        $this->persistenceFactory = $persistenceFactory;
        $this->sectionHandler = $sectionHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getContentHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getSearchHandler();
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
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getContentLanguageHandler();
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
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getObjectStateHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getUserHandler();
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
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getTrashHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getUrlAliasHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        $this->logger->logCall( __METHOD__ );
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
