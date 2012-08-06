<?php
/**
 * File containing the ImageStorage Converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;
use eZ\Publish\SPI\FieldType\FieldStorage,
    eZ\Publish\Core\FieldType\GatewayBasedStorage,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    LogicException,
    PDO;

/**
 * Converter for Image field type external storage
 *
 * The keyword storage ships a list (array) of keywords in
 * $field->value->externalData. $field->value->data is simply empty, because no
 * internal data is store.
 */
class ImageStorage extends GatewayBasedStorage
{
    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     */
    public function __construct( array $gateways, $fileService )
    {
        foreach ( $gateways as $identifier => $gateway )
        {
            $this->addGateway( $identifier, $gateway );
        }
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $nodePathString = $this->getGateway( $context )->getNodePathString( $versionInfo );

        $storedValue = $field->value->externalData;

        $storedValue['path'] = $this->fileService->storeFile(
            $versionInfo,
            $field,
            $nodePathString
        );

        $storedValue = array_merge(
            // Basic value data
            $storedValue,
            // Image meta data
            $this->fileService->getMetaData( $storedValue['path'] ),
            // Content meta data
            array(
                'fieldId' => $field->id,
                'versionNo' => $versionInfo->versionNo,
                'languageCode' => $field->languageCode,
            )
        );

        $field->value->data = $storedValue;

        // Data has been updated and needs to be stored!
        return true;
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        // Not necessary
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
        // @TODO: What about deleting an image? Variants?
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        // @TODO: Correct?
        return null;
    }
}
