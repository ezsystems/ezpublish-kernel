<?php
/**
 * File containing the Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\BinaryFile;
use ezp\Io\ContentType,
    ezp\Io\SysInfo,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository;

/**
 * Binary file handler
 * @todo Handle creation from HTTP
 */
class Handler
{
    /**
     * @var \ezp\Base\BinaryRepository
     */
    protected $binaryRepository;

    public function __construct()
    {
        //@todo Use Binary service available via injected Repository object!
        $this->binaryRepository = new BinaryRepository;
    }

    /**
     * Returns binary repository used by handler
     *
     * @todo See todo in {@link __construct()}
     * @return \ezp\Base\BinaryRepository
     */
    public function getBinaryRepository()
    {
        return $this->binaryRepository;
    }

    /**
     * Creates a {@link \ezp\Io\BinaryFile} object from $localPath.
     * Destination dir is to be something like <storageDir>/original/<MajorFileType>/ .
     * e.g. for an MP3 file (mime-type = audio/mp3) => var/storage/original/audio/ .
     * File name will be a hash with suffix added (if any).
     *
     * @param string $localPath Path to the local file, somewhere accessible in the system
     * @return \ezp\Io\BinaryFile
     */
    public function createFromLocalPath( $localPath )
    {
        $fileInfo = new FileInfo( $localPath );
        $destination = SysInfo::storageDirectory() . '/original/' . $fileInfo->getContentType()->type;

        // Grab suffix and prepend dot
        $fileSuffix = $fileInfo->getExtension();
        if ( $fileSuffix )
            $fileSuffix = '.' . $fileSuffix;
        // Create dest filename hash
        $destFileName = md5( $fileInfo->getBasename( $fileSuffix ) . microtime() . mt_rand() ) . $fileSuffix;
        $destination .= '/' . $destFileName;
        return $this->binaryRepository->createFromLocalFile( $localPath, $destination );
    }

    /**
     * Loads a file from its $filename and $contentType (aka MIME Type)
     *
     * @param string $filename Name of the file to retrieve (including its extension)
     * @param \ezp\Io\ContentType $contentType ContentType object (aka MIME type)
     */
    public function loadFileFromContentType( $filename, ContentType $contentType )
    {
        $dir = SysInfo::storageDirectory() . '/original/' . $contentType->type;
        return $this->binaryRepository->load( "$dir/$filename" );
    }
}
