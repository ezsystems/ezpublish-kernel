<?php

/**
 * File containing the MigrationHandlerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

interface MigrationHandlerInterface
{
    /**
     * Set the from/to handlers based on identifiers.
     * Returns the MigrationHandler.
     *
     * @param string $fromMetadataHandlerIdentifier
     * @param string $fromBinarydataHandlerIdentifier
     * @param string $toMetadataHandlerIdentifier
     * @param string $toBinarydataHandlerIdentifier
     *
     * @return MigrationHandlerInterface
     */
    public function setIODataHandlersByIdentifiers(
        $fromMetadataHandlerIdentifier,
        $fromBinarydataHandlerIdentifier,
        $toMetadataHandlerIdentifier,
        $toBinarydataHandlerIdentifier
    );
}
