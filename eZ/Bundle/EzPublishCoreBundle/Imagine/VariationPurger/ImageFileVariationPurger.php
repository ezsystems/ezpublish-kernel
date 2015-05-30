<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway as ImageStorageGateway;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\Variation\VariationPurger;

/**
 * Purges image aliases based on image files referenced by the Image FieldType.
 *
 * It uses the FieldType's Storage Gateway.
 */
class ImageFileVariationPurger implements VariationPurger
{
    /** @var ImageFileList */
    private $imageFileList;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct( ImageFileList $imageFileList, IOServiceInterface $ioService, VariationPathGenerator $variationPathGenerator )
    {
        $this->imageFileList = $imageFileList;
        $this->ioService = $ioService;
        $this->variationPathGenerator = $variationPathGenerator;
    }

    /**
     * Purge all variations generated for aliases in $aliasName
     *
     * @param array $aliasNames
     */
    public function purge( array $aliasNames )
    {
        foreach ( $this->imageFileList as $imageFile )
        {
            foreach ( $aliasNames as $aliasName )
            {
                $variationPath = $this->variationPathGenerator->getVariationPath( $imageFile, $aliasName );
                try
                {
                    $binaryFile = $this->ioService->loadBinaryFileByUri( $variationPath );
                }
                catch ( BinaryFileNotFoundException $e )
                {
                    continue;
                }
                // $this->ioService->deleteBinaryFile( $binaryFile );
                if ( isset( $this->logger ) )
                {
                    $this->logger->info( "Purging $aliasName variation $variationPath for original image $imageFile" );
                }
            }
        }
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger( $logger )
    {
        $this->logger = $logger;
    }
}
