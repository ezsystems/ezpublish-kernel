<?php
/**
 * File containing the ImageStorage Converter class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use eZ\Publish\Core\Base\Utils\DeprecationWarnerInterface as DeprecationWarner;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The IO Service used to manipulate data
     *
     * @var IOServiceInterface
     */
    protected $IOService;

    /**
     * Path generator
     *
     * @var PathGenerator
     */
    protected $pathGenerator;

    /** @var MetadataHandler */
    protected $imageSizeMetadataHandler;

    /**
     * @var DeprecationWarner
     */
    private $deprecationWarner;

    public function __construct(
        array $gateways,
        IOServiceInterface $IOService,
        PathGenerator $pathGenerator,
        MetadataHandler $imageSizeMetadataHandler,
        DeprecationWarner $deprecationWarner,
        LoggerInterface $logger = null
    )
    {
        parent::__construct( $gateways );
        $this->IOService = $IOService;
        $this->pathGenerator = $pathGenerator;
        $this->imageSizeMetadataHandler = $imageSizeMetadataHandler;
        $this->logger = $logger;
        $this->deprecationWarner = $deprecationWarner;
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
            $targetPath = sprintf(
                '%s/%s',
                $this->pathGenerator->getStoragePathForField(
                    $field->id,
                    $versionInfo->versionNo,
                    $field->languageCode
                ),
                $field->value->externalData['fileName']
            );

            if ( $this->IOService->exists( $targetPath ) )
            {
                $binaryFile = $this->IOService->loadBinaryFile( $targetPath );
            }
            else
            {
                if ( isset( $field->value->externalData['inputUri'] ) )
                {
                    $localFilePath = $field->value->externalData['inputUri'];
                    unset( $field->value->externalData['inputUri'] );
                }
                else
                {
                    $this->deprecationWarner->log(
                        "Using the Image\\Value::\$id property to create images is deprecated. Use 'inputUri'"
                    );
                    $localFilePath = $field->value->externalData['id'];
                }
                $binaryFileCreateStruct = $this->IOService->newBinaryCreateStructFromLocalFile( $localFilePath );
                $binaryFileCreateStruct->id = $targetPath;
                $binaryFile = $this->IOService->createBinaryFile( $binaryFileCreateStruct );
            }
            $field->value->externalData['mimeType'] = $binaryFile->mimeType;
            $field->value->externalData['imageId'] = $versionInfo->contentInfo->id . '-' . $field->id;
            $field->value->externalData['uri'] = $binaryFile->uri;
            $field->value->externalData['id'] = $binaryFile->id;

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
                return false;
            }

            try
            {
                $binaryFile = $this->IOService->loadBinaryFile( $field->value->data['id'] );
                $metadata = $this->IOService->getMetadata( $this->imageSizeMetadataHandler, $binaryFile );
            }
            catch ( NotFoundException $e )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error( "Image with ID {$field->value->data['id']} not found" );
                }
                return false;
            }

            $field->value->data = array_merge(
                $field->value->data,
                $metadata,
                $contentMetaData
            );
            $field->value->externalData = null;
        }

        $this->getGateway( $context )->storeImageReference( $field->value->data['id'], $field->id );

        // Data has been updated and needs to be stored!
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
     *
     * @throws NotFoundException If the stored image path couldn't be retrieved by the IOService
     *
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        if ( $field->value->data !== null )
        {
            $field->value->data['imageId'] = $versionInfo->contentInfo->id . '-' . $field->id;

            try
            {
                $binaryFile = $this->IOService->loadBinaryFile( $field->value->data['id'] );
            }
            catch ( NotFoundException $e )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error( "Image with id {$field->value->data['id']} not found" );
                }
                return;
            }

            $field->value->data['fileSize'] = $binaryFile->size;
            $field->value->data['uri'] = $binaryFile->uri;
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
        /** @var \eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway $gateway */
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
                    try
                    {
                        $binaryFile = $this->IOService->loadBinaryFile( $storedFilePath );
                        $this->IOService->deleteBinaryFile( $binaryFile );
                    }
                    catch ( NotFoundException $e )
                    {
                        if ( isset( $this->logger ) )
                        {
                            $this->logger->error( "Image with id $storedFilePath not found" );
                        }
                    }
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
