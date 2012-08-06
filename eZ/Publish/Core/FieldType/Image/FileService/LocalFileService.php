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
     * Target storage directory
     *
     * @var string
     */
    protected $installDir;

    /**
     * Name of the site the file belongs to
     *
     * @var string
     */
    protected $siteName;

    /**
     * @param string $installDir
     * @param string $siteName
     */
    public function __construct( $installDir, $siteName )
    {
        $this->installDir = $installDir;
        $this->siteName = $siteName;
    }

    /**
     * Store the file identified by $inputPath returning an identifying path
     * for the storage location
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @param string $nodePathString
     * @return string
     */
    public function storeFile( VersionInfo $versionInfo, Field $field, $nodePathString )
    {
        $sourcePath = $field->value->externalData['path'];
        $targetUri = $this->createTargetPath( $versionInfo, $field, $nodePathString );
        $targetPath = $this->installDir . '/' . $targetUri;

        $this->createDirectoryRecursive(
            dirname( $targetPath )
        );

        // @TODO Should we move here?
        $copyResult = copy( $sourcePath, $targetPath );

        if ( false === $copyResult )
        {
            throw new RuntimeException(
                sprintf(
                    'Could not copy "%s" to "%s"',
                    $sourcePath,
                    $targetPath
                )
            );
        }

        return $targetUri;
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
        $metaData = getimagesize( $this->installDir . '/' . $path );

        return array(
            'width' => $metaData[0],
            'height' => $metaData[1],
            'mime' => $metaData['mime'],
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

        $result = mkdir( $directory );

        if ( false === $result )
        {
            throw new  \RuntimeException( "Could not create directory '{$directory}'." );
        }
    }

    /**
     * Creates the target storage path
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @param string $nodePathString
     * @return string
     */
    protected function createTargetPath( VersionInfo $versionInfo, Field $field, $nodePathString )
    {
        return sprintf(
            'var/%s/storage/images/%s%s-%s-%s/%s',
            $this->siteName,  // var/%s
            $nodePathString, // images/%s, note that $nodePathString ends with a "/"
            $field->id, // /%s-
            $versionInfo->versionNo, // -%s-
            $field->languageCode, // -%s
            basename( $field->value->externalData['fileName'] ) // /%s
        );
    }
}
