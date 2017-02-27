<?php

/**
 * File containing the KeywordStorage Gateway.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Keyword\KeywordStorage;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

abstract class Gateway extends StorageGateway
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()
     */
    abstract public function storeFieldData(Field $field, $contentTypeId, VersionInfo $versionInfo);

    /**
     * Sets the list of assigned keywords into $field->value->externalData.
     *
     * @param Field $field
     */
    abstract public function getFieldData(Field $field);

    /**
     * Retrieve the ContentType ID for the given $field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return mixed
     */
    abstract public function getContentTypeId(Field $field);

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::deleteFieldData()
     * @param mixed $fieldId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     */
    abstract public function deleteFieldData($fieldId, VersionInfo $versionInfo = null);
}
