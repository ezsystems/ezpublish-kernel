<?php
/**
 * File containing the eZ\Publish\SPI\IO\Handler interface
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException,
    eZ\Publish\SPI\IO\Handler as IoHandlerInterface,
    eZ\Publish\SPI\IO\BinaryFileUpdateStruct,
    eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * Handler interface for handling of binary files I/O
 */

class DispatcherHandler implements IoHandlerInterface
{
    /**
     * Io\Storage handler instances, {@see __construct()}
     *
     * @var array
     */
    private $config = array();

    /**
     * Creates new object and validates $config param
     *
     * @param array $config Structure of handlers that follows the following format:
     *     array( 'handlers' => array( 'handler' => Handler, .. ), 'default' => Handler )
     *     ie:
     *               array(
     *                   'default' => $handler1,
     *                   'handlers' => array(
     *                       array(
     *                           'handler' => $handler2,
     *                           // match conditions:
     *                           'prefix' => 'var/original/',
     *                           'suffix' => '.gif,.jpg',
     *                           'contains' => 'image-versioned'
     *                       )
     *                   )
     *               )
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $config does not contain default handler that implements
     *         Handler, handlers is unset or empty (hence you could have used default directly), one of the 'patterns'
     *         is unset or empty (hence it could have been default) or a 'handler' item does not implement Handler
     */
    public function __construct( array $config = array() )
    {
        if ( empty( $config['default'] ) || !$config['default'] instanceof IoHandlerInterface )
        {
            throw new InvalidArgumentException( "\$config['default']", "must be of type eZ\\Publish\\SPI\\IO\\Handler" );
        }
        else if ( empty( $config['handlers'] ) )
        {
            throw new InvalidArgumentException( "\$config['handlers']", "must be of type array" );
        }

        // Validate handlers so it does not need to be done on every call to getHandler()
        foreach ( $config['handlers'] as $key => $handler )
        {
            if ( empty( $handler['contains'] ) && empty( $handler['prefix'] ) && empty( $handler['suffix'] ) )
            {
                throw new InvalidArgumentException(
                    "\$config['handlers'][$key][contains|prefix|suffix]",
                    "either of these must be present and of type string"
                );
            }
            else if ( empty( $handler['handler'] ) || !$handler['handler'] instanceof IoHandlerInterface )
            {
                throw new InvalidArgumentException(
                    "\$config['handlers'][$key]['handler']",
                    "must be of type eZ\\Publish\\SPI\\IO\\Handler"
                );
            }
        }

        $this->config = $config;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createFilestruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createFilestruct )
    {
        return $this->getHandler( $createFilestruct->path )->create( $createFilestruct );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path )
    {
        $this->getHandler( $path )->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( $path === $updateFileStruct->path )
            return $this->getHandler( $path )->update( $path, $updateFileStruct );

        // When file path has changed, check if we should move from one handler to another
        $oldHandler = $this->getHandler( $path );
        $newHandler = $this->getHandler( $updateFileStruct->path);
        if ( $oldHandler === $newHandler )
            return $oldHandler->update( $path, $updateFileStruct );

        // Move file from old to new handler
        throw new \Exception( '@TODO: Moving from one io handler to another one is not implemented!' );
        /*$newHandler->create( $updateFile );
        try
        {
            $oldHandler->delete( $path );
        }
        catch ( \Exception $e )
        {
            $newHandler->delete( $updateFile->path );
            throw $e;
        }*/
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $path )
    {
        return $this->getHandler( $path )->exists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $path )
    {
        return $this->getHandler( $path )->load( $path );
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return resource
     */
    public function getFileResource( $path )
    {
        return $this->getHandler( $path )->getFileResource( $path );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        return $this->getHandler( $path )->getFileContents( $path );
    }

    /**
     * Returns the appropriate handler for $path
     *
     * @internal Depends on {@link $config} being validated by {@link __construct()}!
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\Handler
     */
    private function getHandler( $path )
    {
        if ( empty( $this->config['handlers'] ) )
            return $this->config['default'];

        foreach ( $this->config['handlers'] as $handler )
        {
            // Match handler using strpos & strstr for speed, and to avoid having regex in ini files
            if ( !empty( $handler['contains'] ) && strpos( $path, $handler['contains'] ) === false )
            {
                continue;
            }
            else if ( !empty( $handler['prefix'] ) && strpos( $path, $handler['prefix'] ) !== 0 )
            {
                continue;
            }
            else if ( !empty( $handler['suffix'] ) )
            {
                $suffixMatch = false;
                foreach ( explode( ',', $handler['suffix'] ) as $suffix )
                {
                    if ( strstr( $path, $suffix ) === $suffix )
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
            return $handler['handler'];
        }

        return $this->config['default'];
    }
}
