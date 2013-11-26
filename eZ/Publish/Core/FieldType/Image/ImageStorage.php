<?php
/**
 * File containing the ImageStorage Converter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use eZ\Publish\SPI\FieldType\FieldStorage\EventAware;
use eZ\Publish\SPI\FieldType\FieldStorage\Events;
use eZ\Publish\SPI\FieldType\FieldStorage\Event as FieldStorageEvent;

/**
 * External storage handler for images.
 *
 * IO handling:
 *
 * This handler uses two IOService instances, one for published images, the other one for drafts.
 * They can be identical, in which case both are stored using the same service, but if they're different,
 * images can be stored differently depending on their status.
 *
 * Path generation:
 *
 * Each storage engine provides its own path generation class, an instance of PathGenerator.
 * It offers the opportunity to generate different path based on the content's status, published or draft.
 *
 * Event handling:
 *
 * When a content with images is published, the storage handler offers the opportunity to move files from the
 * $draftIOService to the $publishedIOService, depending on data provided by $pathGenerator.
 */
class ImageStorage extends GatewayBasedStorage implements EventAware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Service used to manipulate images
     * @var IOService
     */
    protected $IOService;

    /**
     * Path generator
     * @var PathGenerator
     */
    protected $pathGenerator;

    /** @var MetadataHandler\ImageSize $imageSizeMetadataHandler */
    protected $imageSizeMetadataHandler;

    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     * @param IOService                                   $IOService
     * @param \eZ\Publish\Core\IO\MetadataHandler         $pathGenerator
     * @param \eZ\Publish\Core\IO\MetadataHandler         $imageSizeMetadataHandler
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        array $gateways,
        IOService $IOService,
        PathGenerator $pathGenerator,
        MetadataHandler $imageSizeMetadataHandler,
        LoggerInterface $logger = null
    )
    {
        parent::__construct( $gateways );
        $this->IOService = $IOService;
        $this->pathGenerator = $pathGenerator;
        $this->imageSizeMetadataHandler = $imageSizeMetadataHandler;
        $this->logger = $logger;
    }

    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $contentMetaData = array(
            'fieldId' => $field->id,
            'versionNo' => $versionInfo->versionNo,
            'languageCode' => $field->languageCode,
        );

        // new image
        if ( isset( $field->value->externalData ) )
        {
            $targetPath = $this->pathGenerator->getStoragePathForField(
                $versionInfo->status,
                $field->id,
                $versionInfo->versionNo,
                $field->languageCode
            ) . '/' . $field->value->externalData['fileName'];

            if ( $this->IOService->exists( $targetPath ) )
            {
                $binaryFile = $this->IOService->loadBinaryFile( $targetPath );
            }
            else
            {
                $binaryFileCreateStruct = $this->IOService->newBinaryCreateStructFromLocalFile(
                    $field->value->externalData['id']
                );
                $binaryFileCreateStruct->id = $targetPath;
                $binaryFile = $this->IOService->createBinaryFile( $binaryFileCreateStruct );
            }

            $field->value->externalData['mimeType'] = $binaryFile->mimeType;
            $field->value->externalData['imageId'] = $versionInfo->contentInfo->id . '-' . $field->id;
            $field->value->externalData['uri'] = $binaryFile->uri;
            $field->value->externalData['id'] = ltrim( $binaryFile->uri, '/' );

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
                $binaryFile = $this->IOService->loadBinaryFile(
                    $this->IOService->getExternalPath( $field->value->data['id'] )
                );
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

        // only store if there are no earlier references to this file for this field
        // isn't that a responsibility from the gateway ?
        // Shouldn't the gateway decide how it handles those references, given the Field object ?
        $this->getGateway( $context )->storeImageReference( $field->value->data['id'], $field->id );

        // Data has been updated and needs to be stored
        return true;
    }

    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        if ( $field->value->data !== null )
        {
            // @todo wrap this within a dedicated service that uses the handler + service under the hood
            // Required since images are stored with their full path, e.g. uri with a Legacy compatible IO handler
            $binaryFileId = $this->IOService->getExternalPath( $field->value->data['id'] );
            $field->value->data['imageId'] = $versionInfo->contentInfo->id . '-' . $field->id;

            try
            {
                $binaryFile = $this->IOService->loadBinaryFile( $binaryFileId );
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
                    $binaryFileId = $this->IOService->getExternalPath( $storedFilePath );
                    try
                    {
                        $binaryFile = $this->IOService->loadBinaryFile( $binaryFileId );
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

    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        // @todo: Correct?
        return null;
    }

    public function handleEvent( FieldStorageEvent $event, array $context )
    {
        if ( !$event instanceof Events\PostPublishFieldStorageEvent )
            return false;

        $field = $event->getField();

        // If the path we currently have isn't the path to a draft, we have nothing to do
        // Wrong, we do. We may be dealing with data from another content, which we need to publish...
        // How do we distinguish that from data coming from another field & content ?
        // Path reverse engineering ?
        if ( !$this->pathGenerator->isPathForDraft( $field->value->data['id'] ) )
            return false;

        $nodePathString = $this->getGateway( $context )->getNodePathString( $event->getVersionInfo() );
        $versionInfo = $event->getVersionInfo();
        $publishedPath = $this->pathGenerator->getStoragePathForField(
            $versionInfo->status,
            $field->id,
            $versionInfo->versionNo,
            $field->languageCode,
            $nodePathString
        ) . '/' . $field->value->data['fileName'];

        $binaryFileId = $this->IOService->getExternalPath( $field->value->data['id'] );
        $draftBinaryFile = $this->IOService->loadBinaryFile(
            $binaryFileId
        );

        $publishedFileCreateStruct = new BinaryFileCreateStruct(
            array(
                'id' => $publishedPath,
                'size' => $draftBinaryFile->size,
                'mimeType' => $draftBinaryFile->mimeType,
                'inputStream' => $this->IOService->getFileInputStream( $draftBinaryFile )
            )
        );

        $publishedBinaryFile = $this->IOService->createBinaryFile( $publishedFileCreateStruct );
        $this->IOService->deleteBinaryFile( $draftBinaryFile );
        $this->getGateway( $context )->removeImageReferences(
            ltrim( $draftBinaryFile->uri, '/' ),
            $versionInfo->versionNo,
            $field->id
        );

        $field->value->data['uri'] = $publishedBinaryFile->uri;
        $field->value->data['id'] = ltrim( $publishedBinaryFile->uri, '/' );

        return true;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway
     */
    protected function getGateway( array $context )
    {
        return parent::getGateway( $context );
    }
}
