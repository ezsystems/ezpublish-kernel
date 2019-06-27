<?php

/**
 * File containing the RichText LegacyStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway as UrlGateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends Gateway
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    protected $dbHandler;

    public function __construct(UrlGateway $urlGateway, DatabaseHandler $dbHandler)
    {
        @trigger_error(
            sprintf('%s is deprecated, use %s instead', self::class, DoctrineStorage::class),
            E_USER_DEPRECATED
        );
        parent::__construct($urlGateway);
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
     * Returns a list of Content ids for a list of remote ids.
     *
     * Non-existent ids are ignored.
     *
     * @param array $remoteIds An array of Content remote ids
     *
     * @return array An array of Content ids, with remote ids as keys
     */
    public function getContentIds(array $remoteIds)
    {
        $objectRemoteIdMap = [];

        if (!empty($remoteIds)) {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select('id', 'remote_id')
                ->from('ezcontentobject')
                ->where($q->expr->in('remote_id', $remoteIds));

            $statement = $q->prepare();
            $statement->execute();
            foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $objectRemoteIdMap[$row['remote_id']] = $row['id'];
            }
        }

        return $objectRemoteIdMap;
    }
}
