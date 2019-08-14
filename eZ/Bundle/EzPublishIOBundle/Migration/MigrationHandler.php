<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry;
use Psr\Log\LoggerInterface;

/**
 * The migration handler sets up from/to IO data handlers, and provides logging, for file migrators and listers.
 */
abstract class MigrationHandler implements MigrationHandlerInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry */
    private $metadataHandlerRegistry;

    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerRegistry */
    private $binarydataHandlerRegistry;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $fromMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $fromBinarydataHandler;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $toMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $toBinarydataHandler;

    public function __construct(
        HandlerRegistry $metadataHandlerRegistry,
        HandlerRegistry $binarydataHandlerRegistry,
        LoggerInterface $logger = null
    ) {
        $this->metadataHandlerRegistry = $metadataHandlerRegistry;
        $this->binarydataHandlerRegistry = $binarydataHandlerRegistry;
        $this->logger = $logger;
    }

    public function setIODataHandlersByIdentifiers(
        $fromMetadataHandlerIdentifier,
        $fromBinarydataHandlerIdentifier,
        $toMetadataHandlerIdentifier,
        $toBinarydataHandlerIdentifier
    ) {
        $this->fromMetadataHandler = $this->metadataHandlerRegistry->getConfiguredHandler($fromMetadataHandlerIdentifier);
        $this->fromBinarydataHandler = $this->binarydataHandlerRegistry->getConfiguredHandler($fromBinarydataHandlerIdentifier);
        $this->toMetadataHandler = $this->metadataHandlerRegistry->getConfiguredHandler($toMetadataHandlerIdentifier);
        $this->toBinarydataHandler = $this->binarydataHandlerRegistry->getConfiguredHandler($toBinarydataHandlerIdentifier);

        return $this;
    }

    final protected function logError($message)
    {
        if (isset($this->logger)) {
            $this->logger->error($message);
        }
    }

    final protected function logInfo($message)
    {
        if (isset($this->logger)) {
            $this->logger->info($message);
        }
    }

    final protected function logMissingFile($id)
    {
        $this->logInfo("File with id $id not found");
    }
}
