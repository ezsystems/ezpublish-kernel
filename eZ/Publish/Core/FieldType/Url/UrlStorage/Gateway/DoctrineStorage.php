<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;

use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;
use eZ\Publish\Core\Persistence\Doctrine\Connection;
use PDO;
use RuntimeException;

class DoctrineStorage extends Gateway
{
    const URL_TABLE = 'ezurl';
    const URL_LINK_TABLE = 'ezurl_object_link';

    /**
     * @var \eZ\Publish\Core\Persistence\Doctrine\Connection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function setConnection($connection)
    {
        if (!$connection instanceof Connection) {
            throw new RuntimeException(
                sprintf(
                    '%s::setConnection expects an instance of %s, but %s given',
                    self::class,
                    Connection::class,
                    get_class($connection)
                )
            );
        }

        $this->connection = $connection;
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
            $query = $this->connection->createQueryBuilder();
            $query
                ->select('id', 'url')
                ->from(self::URL_TABLE)
                ->where('id IN (:ids)')
                ->setParameter(':ids', $ids, Connection::PARAM_STR_ARRAY);

            $statement = $query->execute();
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
            $query = $this->connection->createQueryBuilder();
            $query
                ->select('id', 'url')
                ->from(self::URL_TABLE)
                ->where(
                    $query->expr()->in('url', ':urls')
                )
                ->setParameter(':urls', $urls, Connection::PARAM_STR_ARRAY);

            $statement = $query->execute();
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
     * @return int|string
     */
    public function insertUrl($url)
    {
        $time = time();

        $query = $this->connection->createQueryBuilder();

        $query->insert(
            $this->connection->quoteIdentifier(self::URL_TABLE)
        )->values([
            'created' => ':created',
            'modified' => ':modified',
            'original_url_md5' => ':original_url_md5',
            'url' => ':url',
        ])
            ->setParameter(':created', $time, PDO::PARAM_INT)
            ->setParameter(':modified', $time, PDO::PARAM_INT)
            ->setParameter(':original_url_md5', md5($url))
            ->setParameter(':url', $url)
        ;

        $query->execute();

        return $this->connection->lastInsertId(
            $this->connection->getSequenceName(self::URL_TABLE, 'id')
        );
    }

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param int|string $urlId
     * @param int|string $fieldId
     * @param int $versionNo
     */
    public function linkUrl($urlId, $fieldId, $versionNo)
    {
        $query = $this->connection->createQueryBuilder();

        $query->insert(
            $this->connection->quoteIdentifier(self::URL_LINK_TABLE)
        )->values([
            'contentobject_attribute_id' => ':contentobject_attribute_id',
            'contentobject_attribute_version' => ':contentobject_attribute_version',
            'url_id' => ':url_id',
        ])
            ->setParameter(':contentobject_attribute_id', $fieldId, PDO::PARAM_INT)
            ->setParameter(':contentobject_attribute_version', $versionNo, PDO::PARAM_INT)
            ->setParameter(':url_id', $urlId, PDO::PARAM_INT)
        ;

        $query->execute();
    }
    /**
     * Removes link to URL for $fieldId in $versionNo and cleans up possibly orphaned URLs.
     *
     * @param int|string $fieldId
     * @param int $versionNo
     */
    public function unlinkUrl($fieldId, $versionNo)
    {
        $deleteQuery = $this->connection->createQueryBuilder();

        $deleteQuery->delete(
            $this->connection->quoteIdentifier(self::URL_LINK_TABLE)
        )->where(
            $deleteQuery->expr()->andX(
                $deleteQuery->expr()->in('contentobject_attribute_id', ':contentobject_attribute_id'),
                $deleteQuery->expr()->in('contentobject_attribute_version', ':contentobject_attribute_version')
            )
        )
            ->setParameter(':contentobject_attribute_id', $fieldId, PDO::PARAM_INT)
            ->setParameter(':contentobject_attribute_version', $versionNo, PDO::PARAM_INT)
        ;

        $deleteQuery->execute();

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
    private function deleteOrphanedUrls()
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->select('url.id')
            ->from($this->connection->quoteIdentifier(self::URL_TABLE), 'url')
            ->leftJoin('url', $this->connection->quoteIdentifier(self::URL_LINK_TABLE), 'link', 'url.id = link.url_id')
            ->where($query->expr()->isNull('link.url_id'))
        ;
        $statement = $query->execute();
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        if (empty($ids)) {
            return;
        }

        $deleteQuery = $this->connection->createQueryBuilder();

        $deleteQuery
            ->delete($this->connection->quoteIdentifier(self::URL_TABLE))
            ->where($deleteQuery->expr()->in('id', ':ids'))
            ->setParameter(':ids', $ids, Connection::PARAM_STR_ARRAY)
        ;

        $deleteQuery->execute();
    }
}
