<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
use eZ\Publish\Core\Persistence\Cache\URLHandler as CacheUrlHandler;
use eZ\Publish\Core\Persistence\Cache\BookmarkHandler as CacheBookmarkHandler;
use eZ\Publish\Core\Persistence\Cache\NotificationHandler as CacheNotificationHandler;
use eZ\Publish\Core\Persistence\Cache\UserPreferenceHandler as CacheUserPreferenceHandler;
use eZ\Publish\Core\Persistence\Cache\UrlWildcardHandler as CacheUrlWildcardHandler;

/**
 * Persistence Cache Handler class.
 */
class Handler implements PersistenceHandlerInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\SectionHandler */
    protected $sectionHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\ContentHandler */
    protected $contentHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler */
    protected $contentLanguageHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler */
    protected $contentTypeHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\LocationHandler */
    protected $locationHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\UserHandler */
    protected $userHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\TrashHandler */
    protected $trashHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler */
    protected $urlAliasHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\ObjectStateHandler */
    protected $objectStateHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\TransactionHandler */
    protected $transactionHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\URLHandler */
    protected $urlHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\BookmarkHandler */
    protected $bookmarkHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\NotificationHandler */
    protected $notificationHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\UserPreferenceHandler */
    protected $userPreferenceHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\UrlWildcardHandler */
    private $urlWildcardHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger */
    protected $logger;

    /**
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
     * @param \eZ\Publish\Core\Persistence\Cache\URLHandler $urlHandler
     * @param \eZ\Publish\Core\Persistence\Cache\BookmarkHandler $bookmarkHandler
     * @param \eZ\Publish\Core\Persistence\Cache\NotificationHandler $notificationHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UserPreferenceHandler $userPreferenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\UrlWildcardHandler $urlWildcardHandler
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
        CacheUrlHandler $urlHandler,
        CacheBookmarkHandler $bookmarkHandler,
        CacheNotificationHandler $notificationHandler,
        CacheUserPreferenceHandler $userPreferenceHandler,
        CacheUrlWildcardHandler $urlWildcardHandler,
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
        $this->urlHandler = $urlHandler;
        $this->bookmarkHandler = $bookmarkHandler;
        $this->notificationHandler = $notificationHandler;
        $this->userPreferenceHandler = $userPreferenceHandler;
        $this->urlWildcardHandler = $urlWildcardHandler;
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
     */
    public function urlWildcardHandler()
    {
        return $this->urlWildcardHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\TransactionHandler
     */
    public function transactionHandler()
    {
        return $this->transactionHandler;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Cache\URLHandler
     */
    public function urlHandler()
    {
        return $this->urlHandler;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Cache\BookmarkHandler
     */
    public function bookmarkHandler()
    {
        return $this->bookmarkHandler;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Cache\NotificationHandler
     */
    public function notificationHandler()
    {
        return $this->notificationHandler;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Cache\UserPreferenceHandler
     */
    public function userPreferenceHandler()
    {
        return $this->userPreferenceHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->transactionHandler->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->transactionHandler->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->transactionHandler->rollback();
    }
}
