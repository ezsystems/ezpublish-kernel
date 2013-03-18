<?php
/**
 * File containing the ImageStorage Converter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

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
     * The IO Service used to manipulate data
     *
     * @var IOService
     */
    protected $IOService;

    /**
     * Path generator
     *
     * @var PathGenerator
     */
    protected $pathGenerator;

    /** @var  */
    protected $imageSizeMetadataHandler;

    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param IOService $IOService
     * @param PathGenerator $imageSizeMetadataHandler
     * @param MetadataHandler $pathGenerator
     */
    public function __construct( array $gateways, IOService $IOService, PathGenerator $pathGenerator, MetadataHandler $imageSizeMetadataHandler )
    {
        parent::__construct( $gateways );
        $this->IOService = $IOService;
        $this->pathGenerator = $pathGenerator;
        $this->imageSizeMetadataHandler = $imageSizeMetadataHandler;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    /*public function copyLegacyField( VersionInfo $versionInfo, Field $field, Field $originalField, array $context )
    {
        if ( $originalField->value->data === null )
        {
            return false;
        }

        // Field copies don't store their own image, but store their own reference to it
        $this->getGateway( $context )->storeImageReference( $originalField->value->data['path'], $field->id );

        $contentMetaData = array(
            'fieldId' => $field->id,
            'versionNo' => $versionInfo->versionNo,
            'languageCode' => $field->languageCode,
        );

        $storedValue = array_merge(
            // Basic value data
            $field->value->data,
            // Content meta data
            $contentMetaData
        );

        $field->value->data = $storedValue;
        $field->value->externalData = null;

        return true;
    }*/

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /*$storedValue = isset( $field->value->externalData )
            // New image
            ? $field->value->externalData
            // Copied / updated image
            : $field->value->data;*/

        $contentMetaData = array(
            'fieldId' => $field->id,
            'versionNo' => $versionInfo->versionNo,
            'languageCode' => $field->languageCode,
        );

        // new image
        if ( isset( $field->value->externalData ) )
        {
            $targetPath = $this->getFieldPath(
                $field->id,
                $versionInfo->versionNo,
                $field->languageCode,
                $this->getGateway( $context )->getNodePathString( $versionInfo, $field->id )
            ) . '/' . $field->value->externalData['fileName'];

            if ( !$binaryFile = $this->IOService->loadBinaryFile( $targetPath ) )
            {
                $binaryFileCreateStruct = $this->IOService->newBinaryCreateStructFromLocalFile(
                    $field->value->externalData['path']
                );
                $binaryFileCreateStruct->uri = $targetPath;
                $binaryFile = $this->IOService->createBinaryFile( $binaryFileCreateStruct );
            }
            $field->value->externalData['path'] = $this->IOService->getInternalPath( $binaryFile->uri );
            $field->value->externalData['mimeType'] = $binaryFile->mimeType;

            $field->value->data = array_merge(
                $field->value->externalData,
                $this->IOService->getMetadata( $this->imageSizeMetadataHandler, $binaryFile ),
                $contentMetaData
            );

            $field->value->externalData = null;
        }
        // existing image
        else
        {
            if ( $field->value->data === null )
            {
                // Store empty value only with content meta data
                $field->value->data = $contentMetaData;
                return true;
            }

            $binaryFile = $this->IOService->loadBinaryFile( $this->IOService->getExternalPath( $field->value->data['path'] ) );
            $field->value->data = array_merge(
                $field->value->data,
                $this->IOService->getMetadata( $this->imageSizeMetadataHandler, $binaryFile ),
                $contentMetaData
            );
            $field->value->externalData = null;
        }

        $this->getGateway( $context )->storeImageReference( $field->value->data['path'], $field->id );

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
     *
     * @return string
     */
    protected function getFieldPath( $fieldId, $versionNo, $languageCode, $nodePathString )
    {
        return $this->pathGenerator->getStoragePathForField(
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
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @throws NotFoundException If the stored image path couldn't be retrieved by the IOService
     *
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        if ( $field->value->data !== null )
        {
            $path = $this->IOService->getExternalPath( $field->value->data['path'] );
            if ( ( $binaryFile = $this->IOService->loadBinaryFile( $path ) ) === false )
            {
                throw new NotFoundException( '$field->value->data[path]', $path );
            }

            $field->value->data['fileSize'] = $binaryFile->size;
        }
    }

    /**
     * @param array $fieldIds
     * @param array $context
     *
     * @return boolean
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $gateway = $this->getGateway( $context );

        $fieldXmls = $gateway->getXmlForImages( $versionInfo->versionNo, $fieldIds );

        foreach ( $fieldXmls as $fieldId => $xml )
        {
            $storedFiles = $this->extractFiles( $xml );
            if ( $storedFiles === null )
            {
                continue;
            }

            foreach ( $storedFiles as $storedFilePath )
            {
                $gateway->removeImageReferences( $storedFilePath, $versionInfo->versionNo, $fieldId );
                if ( $gateway->countImageReferences( $storedFilePath ) === 0 )
                {
                    $localPath = $this->IOService->getExternalPath( $storedFilePath );
                    $binaryFile = $this->IOService->loadBinaryFile( $localPath );
                    $this->IOService->deleteBinaryFile( $binaryFile );
                }
            }
        }
    }

    /**
     * Extracts the field storage path from  the given $xml string
     *
     * @param string $xml
     *
     * @return string|null
     */
    protected function extractFiles( $xml )
    {
        if ( empty( $xml ) )
        {
            // Empty image value
            return null;
        }

        $files = array();

        $dom = new \DOMDocument();
        $dom->loadXml( $xml );
        if ( $dom->documentElement->hasAttribute( 'dirpath' ) )
        {
            $url = $dom->documentElement->getAttribute( 'url' );
            if ( empty( $url ) )
                return null;

            $files[] = $url;
            /** @var \DOMNode $childNode */
            foreach ( $dom->documentElement->childNodes as $childNode )
            {
                if ( $childNode->nodeName != 'alias' )
                    continue;

                $files[] = $childNode->getAttribute( 'url' );
            }
            return $files;
        }

        return null;
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
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
        // @todo: Correct?
        return null;
    }
}
