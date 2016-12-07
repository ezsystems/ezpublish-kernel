<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReader;

use eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileRowReaderInterface;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

abstract class LegacyStorageFileRowReader implements FileRowReaderInterface
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    private $dbHandler;

    /** @var \PDOStatement */
    private $statement;

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler Database handler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    final public function init()
    {
        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery->select('filename, mime_type')->from($this->dbHandler->quoteTable($this->getStorageTable()));
        $this->statement = $selectQuery->prepare();
        $this->statement->execute();
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
