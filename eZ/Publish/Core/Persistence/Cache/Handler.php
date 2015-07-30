<?php

/**
 * File containing the Persistence Cache Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandlerInterface;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\TransactionHandler as CacheTransactionHandler;
use eZ\Publish\Core\Persistence\Cache\TrashHandler as CacheTrashHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
use eZ\Publish\Core\Persistence\Cache\ObjectStateHandler as CacheObjectStateHandler;

/**
 * Persistence Cache Handler class.
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
     * @var UserHandler
     */
    protected $userHandler;

    /**
     * @var TrashHandler
     */
    protected $trashHandler;

    /**
     * @var UrlAliasHandler
     */
    protected $urlAliasHandler;

    /**
     * @var ObjectStateHandler
     */
    protected $objectStateHandler;

    /**
     * @var TransactionHandler
     */
    protected $transactionHandler;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * Construct the class.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler Must be factory for inner persistence, ie: legacy
     * @param \eZ\Publish\Core\Persistence\Cache\SectionHandler $sectionHandler
     * @param \eZ\Publish\Core\Persistence\Cache\LocationHandler $locationHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentHandler $contentHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler $contentLanguageHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler $contentTypeHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UserHandler $userHandler
     * @param \eZ\Publish\Core\Persistence\Cache\TransactionHandler $transactionHandler
     * @param \eZ\Publish\Core\Persistence\Cache\TrashHandler $trashHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler $urlAliasHandler
     * @param \eZ\Publish\Core\Persistence\Cache\ObjectStateHandler $objectStateHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct(
        PersistenceHandlerInterface $persistenceHandler,
        CacheSectionHandler $sectionHandler,
        CacheLocationHandler $locationHandler,
        CacheContentHandler $contentHandler,
        CacheContentLanguageHandler $contentLanguageHandler,
        CacheContentTypeHandler $contentTypeHandler,
        CacheUserHandler $userHandler,
        CacheTransactionHandler $transactionHandler,
        CacheTrashHandler $trashHandler,
        CacheUrlAliasHandler $urlAliasHandler,
        CacheObjectStateHandler $objectStateHandler,
        PersistenceLogger $logger
    ) {
        $this->persistenceHandler = $persistenceHandler;
        $this->sectionHandler = $sectionHandler;
        $this->locationHandler = $locationHandler;
        $this->contentHandler = $contentHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->userHandler = $userHandler;
        $this->transactionHandler = $transactionHandler;
        $this->trashHandler = $trashHandler;
        $this->urlAliasHandler = $urlAliasHandler;
        $this->objectStateHandler = $objectStateHandler;
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
        return $this->objectStateHandler;
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
     *
     * @todo Create cache implementation so we can avoid injecting persistenceHandler and logger
     */
    public function urlWildcardHandler()
    {
        $this->logger->logUnCachedHandler(__METHOD__);

        return $this->persistenceHandler->urlWildcardHandler();
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\TransactionHandler
     */
    public function transactionHandler()
    {
        return $this->transactionHandler;
    }

    /**
     * Begin transaction.
     *
     * @deprecated Since 5.3 {@use transactionHandler()->beginTransaction()}
     */
    public function beginTransaction()
    {
        $this->transactionHandler->beginTransaction();
    }

    /**
     * Commit transaction.
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     *
     * @deprecated Since 5.3 {@use transactionHandler()->beginTransaction()}
     */
    public function commit()
    {
        $this->transactionHandler->commit();
    }

    /**
     * Rollback transaction.
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     *
     * @deprecated Since 5.3 {@use transactionHandler()->beginTransaction()}
     */
    public function rollback()
    {
        $this->transactionHandler->rollback();
    }
}
