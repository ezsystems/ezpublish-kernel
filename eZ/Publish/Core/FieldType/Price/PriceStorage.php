<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 16/05/14
 * Time: 16:34
 */

namespace eZ\Publish\Core\FieldType\Price;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class PriceStorage extends GatewayBasedStorage
{
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $gateway = $this->getGateway( $context );
        return $gateway->getFieldData( $field );
    }

    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return null;
    }

    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {

    }

    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {

    }

    public function hasFieldData()
    {
        return true;
    }

} 