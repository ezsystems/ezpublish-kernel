<?php

/**
 * File containing the RichText LegacyStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;

class LegacyStorage extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway.
     *
     * @param mixed $dbHandler
     *
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection($dbHandler)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$dbHandler instanceof DatabaseHandler) {
            throw new RuntimeException('Invalid dbHandler passed');
        }

        $this->urlGateway->setConnection($dbHandler);
        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection.
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->dbHandler === null) {
            throw new \RuntimeException('Missing database connection.');
        }

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
        $objectRemoteIdMap = array();

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
