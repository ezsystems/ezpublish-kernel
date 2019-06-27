<?php

/**
 * File containing the Url LegacyStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;
use PDO;

/**
 * Url field type external storage gateway implementation using Zeta Database Component.
 *
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends Gateway
{
    const URL_TABLE = 'ezurl';
    const URL_LINK_TABLE = 'ezurl_object_link';

    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    protected $dbHandler;

    public function __construct(DatabaseHandler $dbHandler)
    {
        @trigger_error(
            sprintf('%s is deprecated, use %s instead', self::class, DoctrineStorage::class),
            E_USER_DEPRECATED
        );
        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        return $this->dbHandler;
    }

    /**
     * Returns a list of URLs for a list of URL ids.
     *
     * Non-existent ids are ignored.
     *
     * @param int[]|string[] $ids An array of URL ids
     *
     * @return array An array of URLs, with ids as keys
     */
    public function getIdUrlMap(array $ids)
    {
        $map = [];

        if (!empty($ids)) {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select('id', 'url')
                ->from(self::URL_TABLE)
                ->where($q->expr->in('id', $ids));

            $statement = $q->prepare();
            $statement->execute();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $map[$row['id']] = $row['url'];
            }
        }

        return $map;
    }

    /**
     * Returns a list of URL ids for a list of URLs.
     *
     * Non-existent URLs are ignored.
     *
     * @param string[] $urls An array of URLs
     *
     * @return array An array of URL ids, with URLs as keys
     */
    public function getUrlIdMap(array $urls)
    {
        $map = [];

        if (!empty($urls)) {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select('id', 'url')
                ->from(self::URL_TABLE)
                ->where($q->expr->in('url', $urls));

            $statement = $q->prepare();
            $statement->execute();
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $map[$row['url']] = $row['id'];
            }
        }

        return $map;
    }

    /**
     * Inserts a new $url and returns its id.
     *
     * @param string $url The URL to insert in the database
     *
     * @return int
     */
    public function insertUrl($url)
    {
        $dbHandler = $this->getConnection();

        $time = time();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable(self::URL_TABLE)
        )->set(
            $dbHandler->quoteColumn('created'),
            $q->bindValue($time, null, PDO::PARAM_INT)
        )->set(
            $dbHandler->quoteColumn('modified'),
            $q->bindValue($time, null, PDO::PARAM_INT)
        )->set(
            $dbHandler->quoteColumn('original_url_md5'),
            $q->bindValue(md5($url))
        )->set(
            $dbHandler->quoteColumn('url'),
            $q->bindValue($url)
        );

        $q->prepare()->execute();

        return $dbHandler->lastInsertId(
            $dbHandler->getSequenceName(self::URL_TABLE, 'id')
        );
    }

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param int $urlId
     * @param int $fieldId
     * @param int $versionNo
     */
    public function linkUrl($urlId, $fieldId, $versionNo)
    {
        $dbHandler = $this->getConnection();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable(self::URL_LINK_TABLE)
        )->set(
            $dbHandler->quoteColumn('contentobject_attribute_id'),
            $q->bindValue($fieldId, null, PDO::PARAM_INT)
        )->set(
            $dbHandler->quoteColumn('contentobject_attribute_version'),
            $q->bindValue($versionNo, null, PDO::PARAM_INT)
        )->set(
            $dbHandler->quoteColumn('url_id'),
            $q->bindValue($urlId, null, PDO::PARAM_INT)
        );

        $q->prepare()->execute();
    }

    /**
     * Removes link to URL for $fieldId in $versionNo and cleans up possibly orphaned URLs.
     *
     * @param int $fieldId
     * @param int $versionNo
     */
    public function unlinkUrl($fieldId, $versionNo)
    {
        $dbHandler = $this->getConnection();

        $deleteQuery = $dbHandler->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $dbHandler->quoteTable(self::URL_LINK_TABLE)
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in($dbHandler->quoteColumn('contentobject_attribute_id'), $fieldId),
                $deleteQuery->expr->in($dbHandler->quoteColumn('contentobject_attribute_version'), $versionNo)
            )
        );

        $deleteQuery->prepare()->execute();

        $this->deleteOrphanedUrls();
    }

    /**
     * Deletes all orphaned URLs.
     *
     * @todo using two queries because zeta Database does not support joins in delete query.
     * That could be avoided if the feature is implemented there.
     *
     * URL is orphaned if it is not linked to a content attribute through ezurl_object_link table.
     */
    protected function deleteOrphanedUrls()
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select(
            $dbHandler->quoteColumn('id', self::URL_TABLE)
        )->from(
            $dbHandler->quoteTable(self::URL_TABLE)
        )->leftJoin(
            $dbHandler->quoteTable(self::URL_LINK_TABLE),
            $query->expr->eq(
                $dbHandler->quoteColumn('url_id', self::URL_LINK_TABLE),
                $dbHandler->quoteColumn('id', self::URL_TABLE)
            )
        )->where(
            $query->expr->isNull(
                $dbHandler->quoteColumn('url_id', self::URL_LINK_TABLE)
            )
        );

        $statement = $query->prepare();
        $statement->execute();
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return;
        }

        $deleteQuery = $dbHandler->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $dbHandler->quoteTable(self::URL_TABLE)
        )->where(
            $deleteQuery->expr->in($dbHandler->quoteColumn('id'), $ids)
        );

        $deleteQuery->prepare()->execute();
    }
}
