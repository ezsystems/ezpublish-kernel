<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User;

use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Description of UserStorage.
 *
 * Methods in this interface are called by storage engine.
 * Proper Gateway and its Connection is injected via Dependency Injection.
 *
 * The User storage handles the following attributes, following the user field
 * type in eZ Publish 4:
 *  - account_key
 *  - has_stored_login
 *  - is_enabled
 *  - is_locked
 *  - last_visit
 *  - login_count
 */
class UserStorage extends GatewayBasedStorage
{
    /**
     * Field Type External Storage Gateway.
     *
     * @var \eZ\Publish\Core\FieldType\User\UserStorage\Gateway
     */
    protected $gateway;

    /**
     * Allows custom field types to store data in an external source (e.g. another DB table).
     *
     * Stores value for $field in an external data source.
     * The whole {@link eZ\Publish\SPI\Persistence\Content\Field} object is passed and its value
     * is accessible through the {@link eZ\Publish\SPI\Persistence\Content\FieldValue} 'value' property.
     * This value holds the data filled by the user as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * $field->id = unique ID from the attribute tables (needs to be generated by
     * database back end on create, before the external data source may be
     * called from storing).
     *
     * Database connection handler is injected into the Gateway via Dependency Injection.
     *
     * This method might return true if $field needs to be updated after storage done here (to store a PK for instance).
     * In any other case, this method must not return anything (null).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return true|null
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        // Only the UserService may update user data
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $field->value->externalData = $this->gateway->getFieldData($field->id);
    }

    /**
     * @param VersionInfo $versionInfo
     * @param array $fieldIds Array of field Ids
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        // Only the UserService may update user data
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}
