<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Dispatcher class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IoHandlerInterface;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Handler interface for handling of binary files I/O
 */
class Dispatcher implements IoHandlerInterface
{
    /**
     * Default Io\Storage handler instance
     *
     * @var \eZ\Publish\SPI\IO\Handler
     */
    private $defaultHandler;

    /**
     * Alternative Io\Storage handler instances, {@see __construct()}
     *
     * @var array
     */
    private $alternativeHandlers = array();

    /**
     * Creates new object and validates $config param
     *
     * @param IoHandlerInterface $defaultHandler
     * @param array $alternativeHandlers Structure of handlers that follows the following format:
     *     array( array( 'handler' => Handler, .. ), .. )
     *     ie:
     *               array(
     *                   array(
     *                       'handler' => $handler1,
     *                       // match conditions:
     *                       'prefix' => 'var/original/',
     *                       'suffix' => '.gif,.jpg',
     *                       'contains' => 'image-versioned'
     *                   ),
     *                   (...)
     *               )
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $config does not contain default handler that implements
     *         Handler, handlers is unset or empty (hence you could have used default directly), one of the 'patterns'
     *         is unset or empty (hence it could have been default) or a 'handler' item does not implement Handler
     */
    public function __construct( IoHandlerInterface $defaultHandler, array $alternativeHandlers )
    {
        if ( empty( $alternativeHandlers ) )
        {
            throw new InvalidArgumentException( "\$config['handlers']", "must be of type array" );
        }

        // Validate handlers so it does not need to be done on every call to getHandler()
        foreach ( $alternativeHandlers as $key => $handlerConfig )
        {
            if ( empty( $handlerConfig['contains'] ) && empty( $handlerConfig['prefix'] ) && empty( $handlerConfig['suffix'] ) )
            {
                throw new InvalidArgumentException(
                    "\$alternativeHandlers[$key][contains|prefix|suffix]",
                    "either of these must be present and of type string"
                );
            }
            else if ( empty( $handlerConfig['handler'] ) || !$handlerConfig['handler'] instanceof IoHandlerInterface )
            {
                throw new InvalidArgumentException(
                    "\$alternativeHandlers[$key]['handler']",
                    "must be of type eZ\\Publish\\SPI\\IO\\Handler"
                );
            }
        }

        $this->defaultHandler = $defaultHandler;
        $this->alternativeHandlers = $alternativeHandlers;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        return $this->getHandler( $createStruct->id )->create( $createStruct );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $binaryFileId )
    {
        $this->getHandler( $binaryFileId )->delete( $binaryFileId );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param mixed $spiBinaryFileId
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( $spiBinaryFileId === $updateFileStruct->uri )
            return $this->getHandler( $spiBinaryFileId )->update( $spiBinaryFileId, $updateFileStruct );

        // When file path has changed, check if we should move from one handler to another
        $oldHandler = $this->getHandler( $spiBinaryFileId );
        $newHandler = $this->getHandler( $updateFileStruct->uri );
        if ( $oldHandler === $newHandler )
            return $oldHandler->update( $spiBinaryFileId, $updateFileStruct );

        // Move file from old to new handler
        throw new \Exception( '@todo: Moving from one io handler to another one is not implemented!' );
        /*$newHandler->create( $updateFile );
        try
        {
            $oldHandler->delete( $path );
        }
        catch ( \Exception $e )
        {
            $newHandler->delete( $updateFile->uri );
            throw $e;
        }*/
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param mixed $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId )
    {
        return $this->getHandler( $spiBinaryFileId )->exists( $spiBinaryFileId );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param mixed $spiBinaryFileId
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        return $this->getHandler( $spiBinaryFileId )->load( $spiBinaryFileId );
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param mixed $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        return $this->getHandler( $spiBinaryFileId )->getFileResource( $spiBinaryFileId );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param mixed $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        return $this->getHandler( $spiBinaryFileId )->getFileContents( $spiBinaryFileId );
    }

    /**
     * Returns the appropriate handler for $path
     *
     * @internal Depends on {@link $config} being validated by {@link __construct()}!
     *
     * @param mixed $binaryFileId
     *
     * @return \eZ\Publish\Core\IO\Handler
     */
    private function getHandler( $binaryFileId )
    {
        foreach ( $this->alternativeHandlers as $handlerConfig )
        {
            // Match handler using strpos & strstr for speed, and to avoid having regex in ini files
            if ( !empty( $handlerConfig['contains'] ) && strpos( $binaryFileId, $handlerConfig['contains'] ) === false )
            {
                continue;
            }

            if ( !empty( $handlerConfig['prefix'] ) && strpos( $binaryFileId, $handlerConfig['prefix'] ) !== 0 )
            {
                continue;
            }

            if ( !empty( $handlerConfig['suffix'] ) )
            {
                $suffixMatch = false;
                foreach ( explode( ',', $handlerConfig['suffix'] ) as $suffix )
                {
                    if ( strstr( $binaryFileId, $suffix ) === $suffix )
                    {
                        $suffixMatch = true;
                        break;
                    }
                }

                if ( !$suffixMatch )
                    continue;
            }
            // Everything matched (incl one of suffixes), and since __construct made sure not all where empty
            // it should be fairly safe to return this handler
            return $handlerConfig['handler'];
        }

        return $this->defaultHandler;
    }

    public function getInternalPath( $spiBinaryFileId )
    {
        // TODO: Implement getInternalPath() method.
    }

    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        // TODO: Implement getMetadata() method.
    }

    public function getExternalPath( $path )
    {
        // TODO: Implement getExternalPath() method
    }

    public function getUri( $spiBinaryFileId )
    {
        return $this->getHandler( $spiBinaryFileId )->getUri( $spiBinaryFileId );
    }
}
