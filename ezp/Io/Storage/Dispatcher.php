<?php
/**
 * File containing the ezp\Io\Handler interface
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Storage;

use ezp\Base\Exception\InvalidArgumentType,
    ezp\Io\Handler as IoHandlerInterface,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\BinaryFileCreateStruct,
    DateTime;

/**
 * Handler interface for handling of binary files I/O
 */

class Dispatcher implements IoHandlerInterface
{
    /**
     * Io\Storage backends instances, {@see __construct()}
     *
     * @var array
     */
    private $config = array();

    /**
     * Creates new object and validates $config param
     *
     * @param array $config Structure of backends that follows the following format:
     *     array( 'backends' => array( 'handler' => Handler, 'match' => array(..) ), 'default' => Handler )
     *     ie:
     *               array(
     *                   'default' => $backend1,
     *                   'backends' => array(
     *                       array(
     *                           'handler' => $backend2,
     *                           'match' => array(
     *                               'prefix' => 'var/original/',
     *                               'suffix' => '.gif,.jpg',
     *                               'contains' => 'image-versioned'
     *                           )
     *                       )
     *                   )
     *               )
     *
     * @throws \ezp\Base\Exception\InvalidArgumentType If $config does not contain default backend that implements
     *         Handler, backends is unset or empty (hence you could have used default directly), one of the 'patterns'
     *         is unset or empty (hence it could have been default) or a 'handler' item does not implement Handler
     */
    public function __construct( array $config = array() )
    {
        if ( empty( $config['default'] ) || !$config['default'] instanceof IoHandlerInterface )
        {
            throw new InvalidArgumentType( "\$config['default']", "ezp\\Io\\Handler" );
        }
        else if ( empty( $config['backends'] ) )
        {
            throw new InvalidArgumentType( "\$config['backends']", "array" );
        }

        // Validate backends so it does not need to be done on every call to getHandler()
        foreach ( $config['backends'] as $key => $backend )
        {
            if ( empty( $backend['match'] ) )
            {
                throw new InvalidArgumentType( "\$config['backends'][$key]['match']", "array" );
            }

            $match = $backend['match'];
            if ( empty( $match['contains'] ) && empty( $match['prefix'] ) && empty( $match['suffix'] ) )
            {
                throw new InvalidArgumentType( "\$config['backends'][$key]['match']['contains/prefix/suffix']", "string" );
            }
            else if ( empty( $backend['handler'] ) || !$backend['handler'] instanceof IoHandlerInterface )
            {
                throw new InvalidArgumentType( "\$config['backends'][$key]['handler']", "ezp\\Io\\Handler" );
            }
        }

        $this->config = $config;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @param \ezp\Io\BinaryFileCreateStruct $file
     * @return \ezp\Io\BinaryFile The newly created BinaryFile object
     * @uses \ezp\Io\Handler::create() To create the binary file in backend
     */
    public function create( BinaryFileCreateStruct $file )
    {
        return $this->getHandler( $file->path )->create( $file );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @param string $path
     * @uses \ezp\Io\Handler::delete() To delete the binary file in backend
     */
    public function delete( $path )
    {
        return $this->getHandler( $path )->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param \ezp\Io\BinaryFileUpdateStruct $updateFile
     * @return \ezp\Io\BinaryFile The updated BinaryFile
     * @uses \ezp\Io\Handler::update() To update the binary file in backend
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
     * @uses \ezp\Io\Handler::exists() To see if file exists in backend
     */
    public function exists( $path )
    {
        return $this->getHandler( $path )->exists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @param string $path
     * @return \ezp\Io\BinaryFile
     * @uses \ezp\Io\Handler::load() To load the binary file from backend
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
     * @uses \ezp\Io\Handler::getFileResource() To get the binary file resource from backend
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
     * @uses \ezp\Io\Handler::getFileContents() To get the binary file content from backend
     */
    public function getFileContents( $path )
    {
        return $this->getHandler( $path )->getFileContents( $path );
    }

    /**
     * Returns the appropriate backend for $path
     *
     * @internal Depends on {@link $config} being validated by {@link __construct()}!
     *
     * @param string $path
     * @return \ezp\Io\Handler
     */
    private function getHandler( $path )
    {
        if ( empty( $this->config['backends'] ) )
            return $this->config['default'];

        foreach ( $this->config['backends'] as $backend )
        {
            // Match backend using strpos & strstr for speed, and to avoid having regex in ini files
            $match = $backend['match'];
            if ( !empty( $match['contains'] ) && strpos( $path, $match['contains'] ) === false )
            {
                continue;
            }
            else if ( !empty( $match['prefix'] ) && strpos( $path, $match['prefix'] ) !== 0 )
            {
                continue;
            }
            else if ( !empty( $match['suffix'] ) )
            {
                $matchSuffix = false;
                foreach ( explode( ',', $match['suffix'] ) as $suffix )
                {
                    if ( strstr( $path, $suffix ) === $suffix )
                    {
                        $matchSuffix = true;
                        break;
                    }
                }

                if ( !$matchSuffix )
                {
                    continue;
                }
            }
            // Everything matched (incl one of suffixes), and since __construct made sure not all where empty
            // it should be fairly safe to return this handler
            return $backend['handler'];
        }

        return $this->config['default'];
    }
}
