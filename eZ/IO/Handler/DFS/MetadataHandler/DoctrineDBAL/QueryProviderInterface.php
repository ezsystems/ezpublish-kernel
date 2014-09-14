<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL;

interface QueryProviderInterface
{
    /**
     * Creates a select query for one row from a path
     * @param string $path
     * @return string
     */
    public function createSelectByPath($path);

    public function createInsert($path,$mtime);
} 
