<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence;

/**
 * The main handler for Storage Engine.
 */
interface Handler
{
    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler();

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\URL\Handler
     */
    public function urlHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Handler
     */
    public function bookmarkHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\Notification\Handler
     */
    public function notificationHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\UserPreference\Handler
     */
    public function userPreferenceHandler();

    /**
     * @return \eZ\Publish\SPI\Persistence\TransactionHandler
     */
    public function transactionHandler();

    /**
     * Begin transaction.
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     *
     * @deprecated Since 5.3 {@use transactionHandler()->beginTransaction()}
     */
    public function beginTransaction();

    /**
     * Commit transaction.
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     *
     * @deprecated Since 5.3 {@use transactionHandler()->commit()}
     */
    public function commit();

    /**
     * Rollback transaction.
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     *
     * @deprecated Since 5.3 {@use transactionHandler()->rollback()}
     */
    public function rollback();
}
