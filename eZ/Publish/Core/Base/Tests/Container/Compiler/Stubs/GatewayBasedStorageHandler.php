<?php

/**
 * File containing the GatewayBasedStorageHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Stub implementation of GatewayBasedStorage.
 */
class GatewayBasedStorageHandler extends GatewayBasedStorage
{
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
    }

    public function hasFieldData()
    {
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}
