<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReader;

use Doctrine\DBAL\Connection;
use eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface;

abstract class LegacyStorageFileRowReader implements FileRowReaderInterface
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Driver\Statement */
    private $statement;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    final public function init()
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select('filename', 'mime_type')
            ->from($this->getStorageTable());
        $this->statement = $selectQuery->execute();
    }

    /**
     * Returns the table name to store data in.
     *
     * @return string
     */
    abstract protected function getStorageTable();

    final public function getRow()
    {
        $row = $this->statement->fetch();

        return $this->prependMimeToPath($row['filename'], $row['mime_type']);
    }

    final public function getCount()
    {
        return $this->statement->rowCount();
    }

    /**
     * Prepends $path with the first part of the given $mimeType.
     *
     * @param string $path
     * @param string $mimeType
     *
     * @return string
     */
    private function prependMimeToPath($path, $mimeType)
    {
        return substr($mimeType, 0, strpos($mimeType, '/')) . '/' . $path;
    }
}
