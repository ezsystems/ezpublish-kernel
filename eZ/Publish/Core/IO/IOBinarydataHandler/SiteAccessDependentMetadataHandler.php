<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\IOBinarydataHandler;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry;
use eZ\Publish\Core\IO\IOMetadataHandler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * @internal
 */
final class SiteAccessDependentMetadataHandler implements IOMetadataHandler
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry */
    private $dataHandlerRegistry;

    public function __construct(
        ConfigResolverInterface $configResolver,
        HandlerRegistry $dataHandlerRegistry
    ) {
        $this->configResolver = $configResolver;
        $this->dataHandlerRegistry = $dataHandlerRegistry;
    }

    protected function getHandler(): IOMetadataHandler
    {
        return $this->dataHandlerRegistry->getConfiguredHandler(
            $this->configResolver->getParameter('io.metadata_handler')
        );
    }

    public function create(BinaryFileCreateStruct $spiBinaryFileCreateStruct)
    {
        return $this->getHandler()->create($spiBinaryFileCreateStruct);
    }

    public function delete($spiBinaryFileId)
    {
        return $this->getHandler()->delete($spiBinaryFileId);
    }

    public function load($spiBinaryFileId)
    {
        return $this->getHandler()->load($spiBinaryFileId);
    }

    public function exists($spiBinaryFileId)
    {
        return $this->getHandler()->exists($spiBinaryFileId);
    }

    public function getMimeType($spiBinaryFileId)
    {
        return $this->getHandler()->getMimeType($spiBinaryFileId);
    }

    public function deleteDirectory($spiPath)
    {
        return $this->getHandler()->deleteDirectory($spiPath);
    }
}
