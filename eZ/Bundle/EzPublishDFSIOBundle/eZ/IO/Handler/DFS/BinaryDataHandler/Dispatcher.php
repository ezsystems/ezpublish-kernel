<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\BinaryDataHandler;

use EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\BinaryDataHandler;

/**
 * Dispatches BinaryData operations based on the file's path
 */
class Dispatcher implements BinaryDataHandler
{
    /** @var Dispatcher\RegistryInterface */
    private $registry = array();

    /**
     * @param Dispatcher\RegistryInterface $registry
     */
    public function __construct( Dispatcher\RegistryInterface $registry )
    {
        $this->registry = $registry;
    }

    /**
     * Creates the file $path with data from $resource
     *
     * @param string   $path
     * @param resource $resource
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If file already exists
     *
     * @return void
     */
    public function createFromStream($path, $resource)
    {
        $this->getHandler($path)->createFromStream($path, $resource);
    }

    /**
     * Retrieves metadata from $path using $metadataHandler
     *
     * @param \eZ\Publish\Core\IO\MetadataHandler $metadataHandler
     * @param string          $path
     *
     * @return array
     */
    public function getMetadata(\eZ\Publish\Core\IO\MetadataHandler $metadataHandler, $path)
    {
        return $this->getHandler($path)->getMetadata($metadataHandler, $path);
    }

    /**
     * Returns the binary content from $path
     *
     * @param $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path is not found
     *
     * @return string
     */
    public function getFileContents($path)
    {
        return $this->getHandler($path)->getFileContents($path);
    }

    /**
     * Returns a read-only, binary file resource to $path
     *
     * @param string $path
     *
     * @return resource A read-only binary resource to $path
     */
    public function getFileResource( $path )
    {
        return $this->getHandler($path)->getFileResource($path);
    }

    /**
     * Updates the content from $path with data from the read binary resource $resource
     *
     * @param string   $path
     * @param resource $resource
     */
    public function updateFileContents( $path, $resource )
    {
        $this->getHandler($path)->updateFileContents($path, $resource);
    }

    /**
     * Deletes the file $path
     * @param string $path
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path isn't found
     */
    public function delete($path)
    {
        $this->getHandler($path)->delete($path);
    }

    /**
     * Renames file $fromPath to $toPath
     *
     * @param $fromPath
     * @param $toPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $toPath already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $fromPath does not exist
     */
    public function rename( $fromPath, $toPath )
    {
        $oldPathHandler = $this->getHandler($fromPath);
        $newPathHandler = $this->getHandler($toPath);

        // same handler, normal rename
        if ($oldPathHandler === $newPathHandler)
        {
            return $oldPathHandler->rename($fromPath, $toPath);
        }
        // different handlers, create on new, delete on old
        else
        {
            $newPathHandler->createFromStream($toPath, $oldPathHandler->getFileResource($fromPath));
            $oldPathHandler->delete($fromPath);
        }
    }

    /**
     * Returns the BinaryDataHandler for $path
     * @param $path
     * @return BinaryDataHandler
     */
    private function getHandler( $path )
    {
        return $this->registry->getHandler( $path );
    }

    /**
     * Returns all the BinaryDataHandlers
     * @return BinaryDataHandler[]
     */
    private function getAllHandlers()
    {
        return $this->registry->getAllHandlers();
    }
    /**
     * Groups file paths from $filePath by handlers.
     *
     * @param array $filePath
     * @param eZDFSFileHandlerDFSBackendInterface[] $handler
     * @param array $handlerClass
     *
     * @return array an array with two sub-arrays
     *               $return['handlers'] is a hash of eZDFSFileHandlerDFSBackendInterface, indexed by handler  class name
     *               $return['files'] is a hash of file path  arrays, indexed by handler class name
     */
    private function mapFilePathArray( array $filePath )
    {
        $map = array( 'handlers' => array(), 'files' => array() );
        foreach ( $filePath as $path )
        {
            $handler = $this->getHandler( $path );
            $handlerClass = get_class( $handler );
            if ( !isset( $map['handlers'][$handlerClass] ) )
            {
                $map['handlers'][$handlerClass] = $handler;
                $map['files'][$handlerClass]= array();
            }

            $map['files'][$handlerClass][] = $path;
        }

        return $map;
    }

    /******************************************************************************************************************/

    /**
     * Creates a copy of $srcFilePath from DFS to $dstFilePath on DFS
     *
     * @param string $srcFilePath Local source file path
     * @param string $dstFilePath Local destination file path
     *
     * @return bool
     */
    public function copyFromDFSToDFS( $srcFilePath, $dstFilePath )
    {
        $srcHandler = $this->getHandler( $srcFilePath );
        $dstHandler = $this->getHandler( $dstFilePath );

        if ( $srcHandler === $dstHandler )
        {
            return $srcHandler->copyFromDFSToDFS( $srcFilePath, $dstFilePath );
        }
        else
        {
            return $dstHandler->createFileOnDFS( $dstFilePath, $srcHandler->getContents( $srcFilePath ) );
        }
    }

    /**
     * Copies the DFS file $srcFilePath to FS
     *
     * @param string $srcFilePath Source file path (on DFS)
     * @param string|bool $dstFilePath Destination file path (on FS). If not specified, $srcFilePath is used
     *
     * @return bool
     */
    public function copyFromDFS( $srcFilePath, $dstFilePath = false )
    {
        return $this->getHandler( $srcFilePath )->copyFromDFS( $srcFilePath, $dstFilePath );
    }

    /**
     * Copies the local file $filePath to DFS under the same name, or a new name
     * if specified
     *
     * @param string      $srcFilePath Local file path to copy from
     * @param bool|string $dstFilePath
     *        Optional path to copy to. If not specified, $srcFilePath is used
     *
     * @return bool
     */
    public function copyToDFS( $srcFilePath, $dstFilePath = false )
    {
        return $this->getHandler( $dstFilePath ?: $srcFilePath )->copyToDFS( $srcFilePath, $dstFilePath );
    }

    /**
     * Sends the contents of $filePath to default output
     *
     * @param string $filePath File path
     * @param int $startOffset Starting offset
     * @param bool|int $length Length to transmit, false means everything
     *
     * @return bool true, or false if operation failed
     */
    public function passthrough( $filePath, $startOffset = 0, $length = false )
    {
        return $this->getHandler( $filePath )->passthrough( $filePath, $startOffset, $length );
    }

    /**
     * Returns the binary content of $filePath from DFS
     *
     * @param string $filePath local file path
     *
     * @return string|bool file's content, or false
     */
    public function getContents( $filePath )
    {
        return $this->getHandler( $filePath )->getContents( $filePath );
    }

    /**
     * Creates the file $filePath on DFS with content $contents
     *
     * @param string $filePath
     * @param string $contents
     *
     * @return bool
     */
    public function createFileOnDFS( $filePath, $contents )
    {
        return $this->getHandler( $filePath )->createFileOnDFS( $filePath, $contents );
    }

    /**
     * Renamed DFS file $oldPath to DFS file $newPath
     *
     * @param string $oldPath
     * @param string $newPath
     *
     * @return bool
     */
    public function renameOnDFS( $oldPath, $newPath )
    {
        $oldPathHandler = $this->getHandler( $oldPath );
        $newPathHandler = $this->getHandler( $newPath );

        // same handler, normal rename
        if ( $oldPathHandler === $newPathHandler )
        {
            return $oldPathHandler->renameOnDFS( $oldPath, $newPath );
        }
        // different handlers, create on new, delete on old
        else
        {
            if ( $newPathHandler->createFileOnDFS( $newPath, $oldPathHandler->getContents( $oldPath ) ) !== true )
                return false;

            return $oldPathHandler->delete( $oldPath );
        }
    }

    /**
     * Checks if a file exists on the DFS
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function existsOnDFS( $filePath )
    {
        return $this->getHandler( $filePath )->existsOnDFS( $filePath );
    }

    /**
     * Returns size of a file in the DFS backend, from a relative path.
     *
     * @param string $filePath The relative file path we want to get size of
     *
     * @return int
     */
    public function getDfsFileSize( $filePath )
    {
        return $this->getHandler( $filePath )->getDfsFileSize( $filePath );
    }

    /**
     * Returns an AppendIterator with every handler's iterator
     *
     * @param string $basePath
     *
     * @return Iterator
     */
    public function getFilesList( $basePath )
    {
        $iterator = new AppendIterator();
        foreach ( $this->getAllHandlers() as $handler )
        {
            $iterator->append( $handler->getFilesList( $basePath ) );
        }
        return $iterator;
    }

    public function applyServerUri( $filePath )
    {
        return $this->getHandler( $filePath )->applyServerUri( $filePath );
    }
}
