<?php

/**
 * File containing the MigrationHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;

abstract class MigrationHandler implements MigrationHandlerInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory */
    private $metadataHandlerFactory;

    /** @var \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory */
    private $binarydataHandlerFactory;

    /** @var string */
    protected $scope;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $fromMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $fromBinarydataHandler;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler */
    protected $toMetadataHandler;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler */
    protected $toBinarydataHandler;

    public function __construct(
        $scope,
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory
    ) {
        $this->scope = $scope;
        $this->metadataHandlerFactory = $metadataHandlerFactory;
        $this->binarydataHandlerFactory = $binarydataHandlerFactory;
    }

    public function setIODataHandlersByIdentifiers(
        $fromMetadataHandlerIdentifier,
        $fromBinarydataHandlerIdentifier,
        $toMetadataHandlerIdentifier,
        $toBinarydataHandlerIdentifier
    ) {
        $this->fromMetadataHandler = $this->metadataHandlerFactory->getConfiguredHandler($fromMetadataHandlerIdentifier);
        $this->fromBinarydataHandler = $this->binarydataHandlerFactory->getConfiguredHandler($fromBinarydataHandlerIdentifier);
        $this->toMetadataHandler = $this->metadataHandlerFactory->getConfiguredHandler($toMetadataHandlerIdentifier);
        $this->toBinarydataHandler = $this->binarydataHandlerFactory->getConfiguredHandler($toBinarydataHandlerIdentifier);

        return $this;
    }
}
