<?php
/**
 * File containing the Legacy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Image\IO;

use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Legacy Image IOService
 *
 * Acts as a dispatcher between the two IOService instances required by FieldType\Image in Legacy.
 * - One is the usual one, as used in ImageStorage, that uses 'images' as the prefix
 * - The other is a special one, that uses 'images-versioned' as the  prefix, in  order to cope with content created
 *   from the backoffice
 *
 * To load a binary file, this service will first try with the normal IOService,
 * and on exception, will fall back to the draft IOService.
 *
 * In addition, loadBinaryFile() will also hide the need to explicitly call getExternalPath()
 * on  the internal path stored in legacy.
 */
class Legacy implements IOServiceInterface
{
    /**
     * Published images IO Service
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $publishedIOService;

    /**
     * Draft images IO Service
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $draftIOService;

    /**
     * Prefix for published images.
     * Example: var/ezdemo_site/storage/images
     * @var string
     */
    private $publishedPrefix;

    /**
     * Prefix for draft images.
     * Example: var/ezdemo_site/storage/images-versioned
     * @var string
     */
    private $draftPrefix;

    /**
     * @param IOServiceInterface $publishedIOService
     * @param IOServiceInterface $draftIOService
     * @param array $options Path options. Known keys: var_dir, storage_dir, draft_images_dir, published_images_dir.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if required options are missing
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *         If any of the passed options has not been defined or does not contain an allowed value
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     *         If a required option is missing.
     */
    public function __construct( IOServiceInterface $publishedIOService, IOServiceInterface $draftIOService, array $options = array() )
    {
        $this->publishedIOService = $publishedIOService;
        $this->draftIOService = $draftIOService;

        $resolver = new OptionsResolver();
        $this->configureOptions( $resolver );
        $this->setPrefixes( $resolver->resolve( $options ) );
    }

    private function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setRequired( array( 'var_dir', 'draft_images_dir', 'published_images_dir' ) );
        $resolver->setOptional( array( 'storage_dir' ) );
        $resolver->setAllowedTypes(
            array(
                'var_dir' => 'string',
                'storage_dir' => 'string',
                'draft_images_dir' => 'string',
                'published_images_dir' => 'string'
            )
        );
    }

    /**
     * Computes the paths to published & draft images path using $options
     *
     * @param array $options
     */
    private function setPrefixes( array $options )
    {
        $pathArray = array( $options['var_dir'] );

        // The storage dir itself might be null
        if ( isset( $options['storage_dir'] ) )
        {
            $pathArray[] = $options['storage_dir'];
        }

        $this->draftPrefix = implode( '/', array_merge( $pathArray, array( $options['draft_images_dir'] ) ) );
        $this->publishedPrefix = implode( '/', array_merge( $pathArray, array( $options['published_images_dir'] ) ) );
    }

    public function getExternalPath( $internalId )
    {
        return $this->publishedIOService->getExternalPath( $internalId );
    }

    public function getMetadata( MetadataHandler $metadataHandler, BinaryFile $binaryFile )
    {
        return $this->publishedIOService->getMetadata( $metadataHandler, $binaryFile );
    }

    public function newBinaryCreateStructFromLocalFile( $localFile )
    {
        return $this->publishedIOService->newBinaryCreateStructFromLocalFile( $localFile );
    }

    public function exists( $binaryFileId )
    {
        return $this->publishedIOService->exists( $binaryFileId );
    }

    public function getInternalPath( $externalId )
    {
        return $this->publishedIOService->getInternalPath( $externalId );
    }

    public function loadBinaryFile( $binaryFileId )
    {
        // If the id is an internal (absolute) path to a draft image, use the draft service to get external path & load
        if ( $this->isDraftImagePath( $binaryFileId ) )
        {
            return $this->draftIOService->loadBinaryFile( $this->draftIOService->getExternalPath( $binaryFileId ) );
        }

        // If the id is an internal path (absolute) to a published image, replace with the internal path
        if ( $this->isPublishedImagePath( $binaryFileId ) )
        {
            $binaryFileId = $this->publishedIOService->getExternalPath( $binaryFileId );
        }

        return $this->publishedIOService->loadBinaryFile( $binaryFileId );
    }

    public function getFileContents( BinaryFile $binaryFile )
    {
        return $this->publishedIOService->getFileContents( $binaryFile );
    }

    public function createBinaryFile( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        return $this->publishedIOService->createBinaryFile( $binaryFileCreateStruct );
    }

    public function getUri( $id )
    {
        return $this->publishedIOService->getUri( $id );
    }

    public function getFileInputStream( BinaryFile $binaryFile )
    {
        return $this->publishedIOService->getFileInputStream( $binaryFile );
    }

    public function deleteBinaryFile( BinaryFile $binaryFile )
    {
        $this->publishedIOService->deleteBinaryFile( $binaryFile );
    }

    public function newBinaryCreateStructFromUploadedFile( array $uploadedFile )
    {
        return $this->publishedIOService->newBinaryCreateStructFromUploadedFile( $uploadedFile );
    }

    /**
     * Checks if $internalPath is a published image path
     * @param string $internalPath
     * @return bool true if $internalPath is the path to a published image
     */
    protected function isPublishedImagePath( $internalPath )
    {
        return strpos( $internalPath, $this->publishedPrefix ) === 0;
    }

    /**
     * Checks if $internalPath is a published image path
     * @param string $internalPath
     * @return bool true if $internalPath is the path to a published image
     */
    protected function isDraftImagePath( $internalPath )
    {
        return strpos( $internalPath, $this->draftPrefix ) === 0;
    }
}
