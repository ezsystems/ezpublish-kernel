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
     * File service to be used
     *
     * @var FileService
     */
    protected $fileService;

    /**
     * Path generator
     *
     * @var PathGenerator
     */
    protected $pathGenerator;

    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param FileService $fileService
     * @param PathGenerator $pathGenerator
     */
    public function __construct( array $gateways, FileService $fileService, PathGenerator $pathGenerator )
    {
        parent::__construct( $gateways );
        $this->fileService = $fileService;
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $storedValue = isset( $field->value->externalData )
            // New image
            ? $field->value->externalData
            // Copied / updated image
            : $field->value->data;

        $contentMetaData = array(
            'fieldId' => $field->id,
            'versionNo' => $versionInfo->versionNo,
            'languageCode' => $field->languageCode,
        );

        if ( $storedValue === null )
        {
            // Store empty value only with content meta data
            $field->value->data = $contentMetaData;
            return true;
        }

        $nodePathString = $this->getGateway( $context )->getNodePathString( $versionInfo, $field->id );

        $targetPath = $this->getFieldPath(
            $field->id,
            $versionInfo->versionNo,
            $field->languageCode,
            $nodePathString
        ) . '/' . $storedValue['fileName'];

        $storedValue['path'] = $this->fileService->storeFile( $storedValue['path'], $targetPath );

        $this->getGateway( $context )->storeImageReference( $storedValue['path'], $field->id );

        $storedValue = array_merge(
            // Basic value data
            $storedValue,
            // Image meta data
            $this->fileService->getMetaData( $storedValue['path'] ),
            // Content meta data
            $contentMetaData
        );

        $field->value->data = $storedValue;

        // Data has been updated and needs to be stored!
        return true;
    }

    /**
     * Returns the path where images for the defined $fieldId are stored
     *
     * @param mixed $fieldId
     * @param int $versionNo
     * @param string $languageCode
     * @param string $nodePathString
     * @return string
     */
    protected function getFieldPath( $fieldId, $versionNo, $languageCode, $nodePathString )
    {
        return 'images/' . $this->pathGenerator->getStoragePathForField(
            $fieldId,
            $versionNo,
            $languageCode,
            $nodePathString
        );
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
        if ( $field->value->data !== null )
        {
            $field->value->data['fileSize'] = $this->fileService->getFileSize( $field->value->data['path'] );
        }
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
        $fieldData = $this->getGateway( $context )->getPathData( $fieldId );
        var_dump( $fieldData );

        foreach ( $fieldData as $fieldDataSet )
        {
            $fieldPath = $this->getFieldPath(
                $fieldDataSet-['fieldId'],
                $fieldDataSet['versionNo'],
                $fieldDataSet['languageCode'],
                $fieldDataSet['nodePathString']
            );

            $storedFieldFiles = $this->fileService->removePath( $fieldPath, true );
        }
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
