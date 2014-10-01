<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishDFSBundle\ApiLoader;

use eZ\Publish\Core\IO\Handler\DFS;
use eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler;
use eZ\Publish\Core\IO\Handler\DFS\MetadataHandler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class DFSFactory
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }

    public function buildDFSIOHandler( MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler )
    {
        $storagePrefix = sprintf(
            '%s/%s',
            rtrim( $this->configResolver->getParameter( 'var_dir' ), '/' ),
            trim( $this->configResolver->getParameter( 'storage_dir' ), '/' )
        );

        return new DFS( $metadataHandler, $binaryDataHandler, $storagePrefix );
    }
}
