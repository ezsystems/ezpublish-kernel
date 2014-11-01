<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\IO\MetadataHandler;
use Psr\Log\LoggerInterface;

/**
 * An extended IOService that tolerates physically missing files.
 *
 * Meant to be used on a "broken" instance where the storage directory isn't in sync with the database.
 *
 * Note that it will still return false when exists() is used.
 */
class TolerantIOService extends IOService
{
    /** @var LoggerInterface */
    protected $logger;

    public function setLogger( LoggerInterface $logger = null )
    {
        $this->logger = $logger;
    }

    /**
     * Deletes $binaryFile
     *
     * @param \eZ\Publish\Core\IO\Values\BinaryFile $binaryFile
     *
     * @throws InvalidArgumentValue If the binary file is invalid
     * @throws BinaryFileNotFoundException If the binary file isn't found
     */
    public function deleteBinaryFile( BinaryFile $binaryFile )
    {
        $this->checkBinaryFileId( $binaryFile->id );
        $spiUri = $this->getPrefixedUri( $binaryFile->id );

        try
        {
            $this->metadataHandler->delete( $spiUri );
        }
        catch ( BinaryFileNotFoundException $e )
        {
            $this->logMissingFile( $binaryFile->uri );
            $logged = true;
        }

        try
        {
            $this->binarydataHandler->delete( $spiUri );
        }
        catch ( BinaryFileNotFoundException $e )
        {
            if ( !isset( $logged ) )
            {
                $this->logMissingFile( $binaryFile->uri );
            }
        }
    }

    /**
     * Loads the binary file with $binaryFileId
     *
     * @param string $binaryFileId
     *
     * @return BinaryFile|MissingBinaryFile
     *
     * @throws InvalidBinaryFileIdException
     */
    public function loadBinaryFile( $binaryFileId )
    {
        $this->checkBinaryFileId( $binaryFileId );

        // @todo An absolute path can in no case be loaded, but throwing an exception is too much (why ?)
        if ( $binaryFileId[0] === '/' )
            return false;

        try
        {
            $spiBinaryFile = $this->metadataHandler->load( $this->getPrefixedUri( $binaryFileId ) );
        }
        catch ( BinaryFileNotFoundException $e )
        {
            $this->logMissingFile( $binaryFileId );

            return $this->createMissingBinaryFile( $binaryFileId );
        }

        if ( !isset( $spiBinaryFile->uri ) )
        {
            $spiBinaryFile->uri = $this->binarydataHandler->getUri( $spiBinaryFile->id );
        }

        return $this->buildDomainBinaryFileObject( $spiBinaryFile );
    }

    public function loadBinaryFileByUri( $binaryFileUri )
    {
        $binaryFileId = $this->removeUriPrefix( $this->binarydataHandler->getIdFromUri( $binaryFileUri ) );
        try
        {
            return $this->loadBinaryFile( $binaryFileId );
        }
        catch ( BinaryFileNotFoundException $e )
        {
            $this->logMissingFile( $binaryFileUri );
            return $this->createMissingBinaryFile( $binaryFileId );
        }
    }

    /**
     * @param $binaryFileId
     *
     * @return MissingBinaryFile
     */
    private function createMissingBinaryFile( $binaryFileId )
    {
        return new MissingBinaryFile(
            array(
                'id' => $binaryFileId,
                'uri' => $this->binarydataHandler->getUri( $binaryFileId )
            )
        );
    }

    private function logMissingFile( $id )
    {
        if ( !isset( $this->logger ) )
        {
            return;
        }
        $this->logger->info( "BinaryFile with id $id not found" );
    }
}
