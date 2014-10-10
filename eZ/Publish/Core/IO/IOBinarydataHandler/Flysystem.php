<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\IOBinarydataHandler;

use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\Core\IO\IOBinarydataHandler;
use eZ\Publish\Core\IO\UrlDecorator;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException as FlysystemNotFoundException;
use League\Flysystem\FilesystemInterface;

class Flysystem implements IOBinaryDataHandler
{
    /** @var FilesystemInterface */
    private $filesystem;

    /**
     * @var UrlDecorator
     */
    private $urlDecorator;

    public function __construct( FilesystemInterface $filesystem, UrlDecorator $urlDecorator = null )
    {
        $this->filesystem = $filesystem;
        $this->urlDecorator = $urlDecorator;
    }

    /**
     * Creates the file $spiBinaryFileId with data from $resource, or updates data if it exists
     *
     * @param BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If file already exists
     *
     * @return void
     */
    public function create( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        try
        {
            $this->filesystem->writeStream(
                $binaryFileCreateStruct->id,
                $binaryFileCreateStruct->getInputStream(),
                array(
                    'mimetype' => $binaryFileCreateStruct->mimeType,
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC
                )
            );
        }
        catch ( FileExistsException $e )
        {
            $this->filesystem->updateStream(
                $binaryFileCreateStruct->id,
                $binaryFileCreateStruct->getInputStream(),
                array(
                    'mimetype' => $binaryFileCreateStruct->mimeType,
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC
                )
            );
        }
    }

    /**
     * Deletes the file $spiBinaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @throws BinaryFileNotFoundException If $spiBinaryFileId isn't found
     */
    public function delete( $spiBinaryFileId )
    {
        try
        {
            $this->filesystem->delete( $spiBinaryFileId );
        }
        catch ( FlysystemNotFoundException $e )
        {
            throw new BinaryFileNotFoundException( $spiBinaryFileId, $e );
        }
    }

    /**
     * Returns the binary content from $spiBinaryFileId
     *
     * @param $spiBinaryFileId
     *
     * @throws BinaryFileNotFoundException If $spiBinaryFileId is not found
     *
     * @return string
     */
    public function getContents( $spiBinaryFileId )
    {
        try
        {
            return $this->filesystem->read( $spiBinaryFileId );
        }
        catch ( FlysystemNotFoundException $e )
        {
            throw new BinaryFileNotFoundException( $spiBinaryFileId, $e );
        }
    }

    /**
     * Returns a read-only, binary file resource to $spiBinaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @return resource A read-only binary resource to $spiBinaryFileId
     *
     * @throws BinaryFileNotFoundException
     */
    public function getResource( $spiBinaryFileId )
    {
        try
        {
            return $this->filesystem->readStream( $spiBinaryFileId );
        }
        catch ( FlysystemNotFoundException $e )
        {
            throw new BinaryFileNotFoundException( $spiBinaryFileId, $e );
        }
    }

    /**
     * Returns the public URI for $spiBinaryFileId using the uriDecorator. If none, prefixes with a /
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getUri( $spiBinaryFileId )
    {
        if ( isset( $this->urlDecorator ) )
            return $this->urlDecorator->decorate( $spiBinaryFileId );
        else
            return '/'. $spiBinaryFileId;
    }

    public function getIdFromUri( $binaryFileUri )
    {
        if ( isset( $this->urlDecorator ) )
            return $this->urlDecorator->undecorate( $binaryFileUri );
        else
            return ltrim( $binaryFileUri, '/' );

    }
}
