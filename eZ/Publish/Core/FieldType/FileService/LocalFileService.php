<?php
/**
 * File containing the LocalFileService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\FileService;
use eZ\Publish\Core\FieldType\FileService;

class LocalFileService implements FileService
{
    /**
     * eZ Publish base install dir
     *
     * @var string
     */
    protected $installDir;

    /**
     * Storage dir path (relative to $installDir)
     *
     * @var string
     */
    protected $storageDir;

    /**
     * Identifier prefix, will be appended to $storageDir for storing, but also
     * delivered as part of the storage identifier.
     *
     * @var string
     */
    protected $identifierPrefix;

    /**
     * @param string $installDir
     * @param string $siteName
     */
    public function __construct( $installDir, $storageDir, $identifierPrefix = '' )
    {
        $this->installDir = $installDir;
        $this->storageDir = $storageDir;
        $this->identifierPrefix = $identifierPrefix;
    }

    /**
     * Returns the full path for $path
     *
     * @param string $path
     * @return string
     */
    protected function getFullPath( $path, $allowLocal = false )
    {
        if ( $allowLocal && substr( $path, 0, 1 ) === '/' )
        {
            return $path;
        }
        return $this->installDir . '/' . ( !empty( $this->storageDir ) ? $this->storageDir . '/' : '' ) . $path;
    }

    /**
     * Store the local file identified by $sourcePath in a location that corresponds
     * to $storageIdentifier. Returns an $storageIdentifier again.
     *
     * @param string $sourcePath
     * @param string $storageIdentifier
     * @return string
     */
    public function storeFile( $sourcePath, $storageIdentifier )
    {
        $fullSourcePath = $this->getFullPath( $sourcePath, true );
        $fullTargetPath = $this->getFullPath( $storageIdentifier );

        if ( $fullSourcePath == $fullTargetPath )
        {
            // Updating the field, no copy needed
            return $storageIdentifier;
        }

        $this->createDirectoryRecursive(
            dirname( $fullTargetPath )
        );

        $copyResult = copy( $fullSourcePath, $fullTargetPath );

        if ( false === $copyResult )
        {
            throw new \RuntimeException(
                sprintf(
                    'Could not copy "%s" to "%s"',
                    $fullSourcePath,
                    $fullTargetPath
                )
            );
        }

        $chmodResult = chmod( $fullTargetPath, 0664 );

        if ( false === $chmodResult )
        {
            throw new \RuntimeException(
                sprintf(
                    'Could not change permissions of "%s" to "%s"',
                    $fullTargetPath,
                    '0644'
                )
            );
        }

        return $storageIdentifier;
    }

    /**
     * Removes the path identified by $storageIdentifier, potentially
     * $recursive.
     *
     * Attemts to removed the path identified by $storageIdentifier. If
     * $storageIdentifier is a directory which is not empty and $recursive is
     * set to false, an exception is thrown. Attemting to remove a non
     * existing $storageIdentifier is silently ignored.
     *
     * @param string $storageIdentifier
     * @param bool $recursive
     * @return void
     * @throws \RuntimeException if children of $storageIdentifier exist and
     *                           $recursive is false
     * @throws \RuntimeException if $storageIdentifier could not be removed (most
     *                           likely permission issues)
     */
    public function remove( $storageIdentifier, $recursive = false )
    {
        $fullPath = $this->getFullPath( $storageIdentifier );

        $this->removePathInternal( $fullPath, $recursive );
    }

    /**
     * Deletes $path, $recursive or not
     *
     * @param string $path
     * @param bool $recursive
     * @return void
     * @throws RuntimeException if $path is a non-empty directory and
     *                          $recursive is false
     * @throws RuntimeException if errors occure during removal
     */
    protected function removePathInternal( $path, $recursive )
    {
        if ( is_dir( $path ) )
        {
            $iterator = new \FilesystemIterator(
                $path,
                \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS
            );
            foreach ( $iterator as $childPath => $fileInfo )
            {
                if ( !$recursive )
                {
                    throw new \RuntimeException(
                        sprintf(
                            'Cannot remove "%s", because directory is not empty.',
                            $path
                        )
                    );
                }
                $this->removePathInternal( $childPath, $recursive );
            }

            $rmdirResult = @rmdir( $path );
            if ( false === $rmdirResult )
            {
                throw new \RuntimeException(
                    sprintf( 'Could not remove directory "%s"', $path )
                );
            }
        }
        else if ( is_file( $path ) )
        {
            $unlinkResult = @unlink( $path );
            if ( false === $unlinkResult )
            {
                throw new \RuntimeException(
                    sprintf( 'Could not remove file "%s"', $path )
                );
            }
        }
        // If target does not exist, ignore it
    }

    /**
     * Returns a storage identifier for the given $path
     *
     * The storage identifier is used to identify $path inside the storage
     * encapsulated by the file service.
     *
     * @param string $path
     * @return string
     */
    public function getStorageIdentifier( $path )
    {
        return ( !empty( $this->identifierPrefix )
            ? $this->identifierPrefix . '/'
            : '' ) . $path;
    }

    /**
     * Returns a hash of meta data
     *
     * array(
     *  'width' => <int>,
     *  'height' => <int>,
     *  'mime' => <string>,
     * );
     *
     * @param string $storageIdentifier
     * @return array
     */
    public function getMetaData( $storageIdentifier )
    {
        // Does not depend on GD
        $metaData = getimagesize( $this->getFullPath( $storageIdentifier ) );

        return array(
            'width' => $metaData[0],
            'height' => $metaData[1],
            'mime' => $metaData['mime'],
        );
    }

    /**
     * Returns the file size of the file identified by $storageIdentifier
     *
     * @param string $storageIdentifier
     * @return int
     */
    public function getFileSize( $storageIdentifier )
    {
        return filesize(
            $this->getFullPath( $storageIdentifier )
        );
    }

    /**
     * Returns is a file/directory with the given $storageIdentifier exists
     *
     * @param string $storageIdentifier
     * @return bool
     */
    public function exists( $storageIdentifier )
    {
        return file_exists( $this->getFullPath( $storageIdentifier ) );
    }

    /**
     * Creates the given directory recursively
     *
     * @param string $directory
     * @return void
     * @throws RuntimeException if the $directory could not be created
     */
    protected function createDirectoryRecursive( $directory )
    {
        if ( is_dir( $directory ) )
        {
            return;
        }

        if ( $directory === '' )
        {
            throw new \RuntimeException( "Unable to create empty directory!" );
        }

        $this->createDirectoryRecursive( dirname( $directory ) );

        $result = @mkdir( $directory, 0775 );

        if ( false === $result )
        {
            throw new  \RuntimeException( "Could not create directory '{$directory}'." );
        }

        $chmodResult = @chmod( $directory, 0775 );

        if ( false === $chmodResult )
        {
            throw new  \RuntimeException(
                "Could not set permissions 0775 on directory '{$directory}'."
            );
        }
    }
}
