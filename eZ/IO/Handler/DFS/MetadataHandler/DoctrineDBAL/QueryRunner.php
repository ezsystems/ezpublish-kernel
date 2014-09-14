<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL;

use Doctrine\DBAL\Connection;

class QueryRunner implements QueryRunnerInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function selectOne( $query )
    {
        $stmt = $this->db->executeQuery($query);
        if ($stmt->rowCount() == 0)
        {
            throw new \Exception('file', $path);
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function insertOne( $path, $size, $mtime, $type = null, $scope = null )
    {
    }
}
