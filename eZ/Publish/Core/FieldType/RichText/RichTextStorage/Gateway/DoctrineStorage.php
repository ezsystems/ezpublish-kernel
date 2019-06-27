<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway as UrlGateway;

class DoctrineStorage extends Gateway
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    public function __construct(UrlGateway $urlGateway, Connection $connection)
    {
        parent::__construct($urlGateway);
        $this->connection = $connection;
    }

    /**
     * Return a list of Content ids for a list of remote ids.
     *
     * Non-existent ids are ignored.
     *
     * @param string[] $remoteIds An array of Content remote ids
     *
     * @return int[] An array of Content ids, with remote ids as keys
     */
    public function getContentIds(array $remoteIds)
    {
        $objectRemoteIdMap = [];

        if (!empty($remoteIds)) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->select(
                    $this->connection->quoteIdentifier('id'),
                    $this->connection->quoteIdentifier('remote_id')
                )
                ->from('ezcontentobject')
                ->where($query->expr()->in('remote_id', ':remoteIds'))
                ->setParameter(':remoteIds', $remoteIds, Connection::PARAM_STR_ARRAY)
            ;

            $statement = $query->execute();
            foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $objectRemoteIdMap[$row['remote_id']] = $row['id'];
            }
        }

        return $objectRemoteIdMap;
    }
}
