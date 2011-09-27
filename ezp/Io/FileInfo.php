<?php
/**
 * File containing the FileInfo class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;
use ezp\Io\ContentType,
    SplFileInfo;

/**
 * FileInfo allows to extract useful information from a file.
 */
class FileInfo extends SplFileInfo
{
    /**
     * Content type (aka MimeType) for file
     *
     * @var \ezp\Io\ContentType
     */
    protected $contentType;

    /**
     * Returns content type (aka MimeType) for file
     *
     * @return \ezp\Io\ContentType
     */
    public function getContentType()
    {
        if ( !isset( $this->contentType ) )
        {
            $this->contentType = ContentType::getFromPath( $this->getPathname() );
        }

        return $this->contentType;
    }

    /**
     * Returns file extension, without the dot.
     *
     * @return string
     */
    public function getExtension()
    {
        if ( method_exists( '\SplFileInfo', 'getExtension' ) )
        {
            return parent::getExtension();
        }

        return pathinfo( $this->getBasename(), PATHINFO_EXTENSION );
    }
}
