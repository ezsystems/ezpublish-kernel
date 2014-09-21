<?php
/**
 * This file is part of the DFSIOHandlerBundle
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler\Dispatcher;

use eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler;

interface RegistryInterface
{
    /**
     * Returns the BinaryDataHandler for $path
     * @param $path
     * @return BinaryDataHandler
     */
    public function getHandler( $path );
}
