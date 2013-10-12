<?php
/**
 * File containing the DFS class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace BD\Bundle\DFSBundle\eZ\IO\Handler;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Handler as IOHandler;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;

class DFS implements IOHandler
{
    /** @var FSInterface */
    protected $fs;

    /** @var DBInterface */
    protected $db;

    /** @var string */
    protected $storagePrefix;

    /**
     * @param StoragePrefix $storagePrefix
     * @param DBInterface $db
     * @param FSInterface $fs
     */
    public function construct( StoragePrefix $storagePrefix, DBInterface $db, FSInterface $fs )
    {
        $this->storagePrefix = $storagePrefix;
        $this->db = $db;
        $this->fs = $fs;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws InvalidArgumentException If the target path already exists
     *
     * @param BinaryFileCreateStruct $createStruct
     *
     * @return BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        $path = $this->addStoragePrefix( $createStruct->id );
        $this->db->insert( $path, $createStruct->mtime );
        $this->fs->createFromStream( $path, $createStruct->getInputStream() );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws NotFoundException If the file doesn't exist
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        $path = $this->addStoragePrefix( $spiBinaryFileId );
        $this->db->delete( $path );
        $this->fs->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws NotFoundException If the source path doesn't exist
     * @throws InvalidArgumentException If the target path already exists
     *
     * @param string $spiBinaryFileId
     * @param BinaryFileUpdateStruct $updateFileStruct
     *
     * @return BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct )
    {
        // TODO: Implement update() method.
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId )
    {
        // TODO: Implement exists() method.
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        // TODO: Implement load() method.
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        // TODO: Implement getFileResource() method.
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        // TODO: Implement getFileContents() method.
    }

    /**
     * Returns the internal, handler level path from the api level $binaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getInternalPath( $spiBinaryFileId )
    {
        // TODO: Implement getInternalPath() method.
    }

    /**
     * Removes the internal storage path from $path
     *
     * @param string $apiBinaryFileId
     *
     * @return string
     */
    public function getExternalPath( $apiBinaryFileId )
    {
        // TODO: Implement getExternalPath() method.
    }

    /**
     * Executes $metadataHandler on $path, and returns the metadata array
     *
     * @param MetadataHandler $metadataHandler
     * @param string $spiBinaryFileId
     *
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        // TODO: Implement getMetadata() method.
    }

    /**
     * Returns the file's public HTTP URI, as exposed from the outside
     * @deprecated should not be required. Seek & destroy.
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getUri( $spiBinaryFileId )
    {
        // TODO: Implement getUri() method.
    }
}
