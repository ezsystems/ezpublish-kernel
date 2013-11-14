<?php
/**
 * File containing the GatewayBasedStorageHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Stubs;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Stub implementation of GatewayBasedStorage.
 */
class GatewayBasedStorageHandler extends GatewayBasedStorage
{
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {

    }

    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {

    }

    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {

    }

    public function hasFieldData()
    {

    }

    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {

    }
}
