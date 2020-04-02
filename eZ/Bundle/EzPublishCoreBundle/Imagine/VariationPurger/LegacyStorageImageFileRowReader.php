<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use Doctrine\DBAL\Connection;

class LegacyStorageImageFileRowReader implements ImageFileRowReader
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Driver\Statement */
    private $statement;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function init()
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery->select('filepath')->from('ezimagefile');
        $this->statement = $selectQuery->execute();
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
