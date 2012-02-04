<?php
/**
 * File containing the eZ\Publish\SPI\IO\Handler interface
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Dispatcher;

use ezp\Base\Exception\InvalidArgumentType,
    eZ\Publish\SPI\IO\Handler as IoHandlerInterface,
    eZ\Publish\SPI\IO\BinaryFileUpdateStruct,
    eZ\Publish\SPI\IO\BinaryFileCreateStruct,
    DateTime;

/**
 * Handler interface for handling of binary files I/O
 */

class Handler implements IoHandlerInterface
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
     * @throws \ezp\Base\Exception\InvalidArgumentType If $config does not contain default handler that implements
     *         Handler, handlers is unset or empty (hence you could have used default directly), one of the 'patterns'
     *         is unset or empty (hence it could have been default) or a 'handler' item does not implement Handler
     */
    public function __construct( array $config = array() )
    {
        if ( empty( $config['default'] ) || !$config['default'] instanceof IoHandlerInterface )
        {
            throw new InvalidArgumentType( "\$config['default']", "eZ\\Publish\\SPI\\IO\\Handler" );
        }
        else if ( empty( $config['handlers'] ) )
        {
            throw new InvalidArgumentType( "\$config['handlers']", "array" );
        }

        // Validate handlers so it does not need to be done on every call to getHandler()
        foreach ( $config['handlers'] as $key => $handler )
        {
            if ( empty( $handler['contains'] ) && empty( $handler['prefix'] ) && empty( $handler['suffix'] ) )
            {
                throw new InvalidArgumentType( "\$config['handlers'][$key][contains|prefix|suffix]", "string" );
            }
            else if ( empty( $handler['handler'] ) || !$handler['handler'] instanceof IoHandlerInterface )
            {
                throw new InvalidArgumentType( "\$config['handlers'][$key]['handler']", "eZ\\Publish\\SPI\\IO\\Handler" );
            }
        }

        $this->config = $config;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $file
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     * @uses \eZ\Publish\SPI\IO\Handler::create() To create the binary file in handler
     */
    public function create( BinaryFileCreateStruct $file )
    {
        return $this->getHandler( $file->path )->create( $file );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @param string $path
     * @uses \eZ\Publish\SPI\IO\Handler::delete() To delete the binary file in handler
     */
    public function delete( $path )
    {
        return $this->getHandler( $path )->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFile
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     * @uses \eZ\Publish\SPI\IO\Handler::update() To update the binary file in handler
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        if ( $path === $updateFile->path )
            return $this->getHandler( $path )->update( $path, $updateFile );

        // When file path has changed, check if we should move from one handler to another
        $oldHandler = $this->getHandler( $path );
        $newHandler = $this->getHandler( $updateFile->path);
        if ( $oldHandler === $newHandler )
            return $oldHandler->update( $path, $updateFile );

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
     * @return bool
     * @uses \eZ\Publish\SPI\IO\Handler::exists() To see if file exists in handler
     */
    public function exists( $path )
    {
        return $this->getHandler( $path )->exists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @param string $path
     * @return \eZ\Publish\SPI\IO\BinaryFile
     * @uses \eZ\Publish\SPI\IO\Handler::load() To load the binary file from handler
     */
    public function load( $path )
    {
        return $this->getHandler( $path )->load( $path );
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     * @return resource
     * @uses \eZ\Publish\SPI\IO\Handler::getFileResource() To get the binary file resource from handler
     */
    public function getFileResource( $path )
    {
        return $this->getHandler( $path )->getFileResource( $path );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @param string $path
     * @return string
     * @uses \eZ\Publish\SPI\IO\Handler::getFileContents() To get the binary file content from handler
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
