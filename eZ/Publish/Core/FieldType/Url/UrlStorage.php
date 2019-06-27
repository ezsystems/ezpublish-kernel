<?php

/**
 * File containing the UrlStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Url;

use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use Psr\Log\LoggerInterface;

/**
 * Converter for Url field type external storage.
 */
class UrlStorage extends GatewayBasedStorage
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway */
    protected $gateway;

    /**
     * Construct from gateways.
     *
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $gateway
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(StorageGateway $gateway, LoggerInterface $logger = null)
    {
        parent::__construct($gateway);
        $this->logger = $logger;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return bool|mixed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $url = $field->value->externalData;

        if (empty($url)) {
            return false;
        }

        $map = $this->gateway->getUrlIdMap([$url]);

        if (isset($map[$url])) {
            $urlId = $map[$url];
        } else {
            $urlId = $this->gateway->insertUrl($url);
        }

        $this->gateway->linkUrl($urlId, $field->id, $versionInfo->versionNo);

        $field->value->data['urlId'] = $urlId;

        // Signals that the Value has been modified and that an update is to be performed
        return true;
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
        $id = $field->value->data['urlId'];
        if (empty($id)) {
            $field->value->externalData = null;

            return;
        }

        $map = $this->gateway->getIdUrlMap([$id]);

        // URL id is not in the DB
        if (!isset($map[$id]) && isset($this->logger)) {
            $this->logger->error("URL with ID '{$id}' not found");
        }

        $field->value->externalData = isset($map[$id]) ? $map[$id] : '';
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        foreach ($fieldIds as $fieldId) {
            $this->gateway->unlinkUrl($fieldId, $versionInfo->versionNo);
        }
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
