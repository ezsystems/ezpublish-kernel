<?php
/**
 * File containing the LocalFileService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\FileService;
use eZ\Publish\Core\FieldType\Image\FileService,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

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
     * @param string $installDir
     * @param string $siteName
     */
    public function __construct( $installDir, $storageDir )
    {
        $this->installDir = $installDir;
        $this->storageDir = $storageDir;
    }

    /**
     * Returns the full path for $path
     *
     * @param string $path
     * @return string
     */
    protected function getFullPath( $path )
    {
        if ( substr( $path, 0, 1 ) === '/' )
        {
            return $path;
        }
        return $this->installDir . '/' . $path;
    }

    /**
     * Store the file identified by $sourcePath in $targetPath.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @return string
     */
    public function storeFile( $sourcePath, $targetPath )
    {
        $targetPath = $this->createInternalPath( $targetPath );

        $fullSourcePath = $this->getFullPath( $sourcePath );
        $fullTargetPath = $this->getFullPath( $targetPath );

        if ( $fullSourcePath == $fullTargetPath )
        {
            // Updating the field, no copy needed
            return $targetPath;
        }

        $this->createDirectoryRecursive(
            dirname( $fullTargetPath )
        );

        $copyResult = copy( $fullSourcePath, $fullTargetPath );

        if ( false === $copyResult )
        {
            throw new RuntimeException(
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
            throw new RuntimeException(
                sprintf(
                    'Could not change permissions of "%s" to "%s"',
                    $fullTargetPath,
                    '0644'
                )
            );
        }

        return $targetPath;
    }

    /**
     * Returns an internal, relative path
     *
     * @param string $path
     * @return string
     */
    protected function createInternalPath( $path )
    {
        return $this->storageDir . '/' . $path;
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
     * @param string $path
     * @return array
     */
    public function getMetaData( $path )
    {
        // Does not depend on GD
        $metaData = getimagesize( $this->getFullPath( $path ) );

        return array(
            'width' => $metaData[0],
            'height' => $metaData[1],
            'mime' => $metaData['mime'],
        );
    }

    /**
     * Returns the file size of the file identified by $path
     *
     * @param string $path
     * @return int
     */
    public function getFileSize( $path )
    {
        return filesize(
            $this->getFullPath( $path )
        );
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

        $result = mkdir( $directory, 0775 );

        if ( false === $result )
        {
            throw new  \RuntimeException( "Could not create directory '{$directory}'." );
        }
    }
}
