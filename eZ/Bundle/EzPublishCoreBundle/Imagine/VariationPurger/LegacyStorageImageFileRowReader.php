<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

class LegacyStorageImageFileRowReader implements ImageFileRowReader
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    private $dbHandler;

    /** @var \PDOStatement */
    private $statement;

    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    public function init()
    {
        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery->select('filepath')->from($this->dbHandler->quoteTable('ezimagefile'));
        $this->statement = $selectQuery->prepare();
        $this->statement->execute();
    }

    public function getRow()
    {
        return $this->statement->fetchColumn(0);
    }

    public function getCount()
    {
        return $this->statement->rowCount();
    }
}
