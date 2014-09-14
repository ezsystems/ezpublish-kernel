<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL;


interface QueryRunnerInterface
{
    public function selectOne($query);

    public function insertOne($path, $size, $mtime, $type = null, $scope = null);
} 
