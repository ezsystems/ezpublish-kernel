<?php
/**
 * File containing the BinaryLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\IO\IOServiceInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

/**
 * Binary loader using eZ IOService.
 * To be used by LiipImagineBundle.
 */
class BinaryLoader implements LoaderInterface
{
    /**
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $ioService;

    /**
     * @var \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    private $extensionGuesser;

    public function __construct( IOServiceInterface $ioService, ExtensionGuesserInterface $extensionGuesser )
    {
        $this->ioService = $ioService;
        $this->extensionGuesser = $extensionGuesser;
    }

    public function find( $path )
    {
        if ( !$this->ioService->exists( $path ) )
        {
            throw new NotLoadableException( "Source image not found in $path" );
        }

        $binaryFile = $this->ioService->loadBinaryFile( $path );
        $mimeType = $binaryFile->mimeType;
        return new Binary(
            $this->ioService->getFileContents( $binaryFile ),
            $mimeType,
            $this->extensionGuesser->guess( $mimeType )
        );
    }
}
