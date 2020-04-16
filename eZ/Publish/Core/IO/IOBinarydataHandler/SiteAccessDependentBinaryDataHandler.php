<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\IOBinarydataHandler;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry;
use eZ\Publish\Core\IO\IOBinarydataHandler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * @internal
 */
final class SiteAccessDependentBinaryDataHandler implements IOBinaryDataHandler
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

    private function getHandler(): IOBinarydataHandler
    {
        return $this->dataHandlerRegistry->getConfiguredHandler(
            $this->configResolver->getParameter('io.binarydata_handler')
        );
    }

    public function create(BinaryFileCreateStruct $binaryFileCreateStruct)
    {
        return $this->getHandler()->create($binaryFileCreateStruct);
    }

    public function delete($spiBinaryFileId)
    {
        return $this->getHandler()->delete($spiBinaryFileId);
    }

    public function getContents($spiBinaryFileId)
    {
        return $this->getHandler()->getContents($spiBinaryFileId);
    }

    public function getResource($spiBinaryFileId)
    {
        return $this->getHandler()->getResource($spiBinaryFileId);
    }

    public function getUri($spiBinaryFileId)
    {
        return $this->getHandler()->getUri($spiBinaryFileId);
    }

    public function getIdFromUri($binaryFileUri)
    {
        return $this->getHandler()->getIdFromUri($binaryFileUri);
    }

    public function deleteDirectory($spiPath)
    {
        return $this->getHandler()->deleteDirectory($spiPath);
    }
}
