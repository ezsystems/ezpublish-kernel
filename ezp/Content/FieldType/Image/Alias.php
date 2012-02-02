<?php
/**
 * File containing the Image Alias class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use eZ\Publish\SPI\Persistence\ValueObject,
    ezp\Base\Exception\PropertyNotFound;

/**
 * Class representing an image alias
 *
 * @property-read \ezp\Io\ContentType $mimeType
 * @property-read string $filename The name of the file (for example "my_image.png").
 * @property-read string $suffix The file suffix, aka "extension" (for example "png").
 * @property-read string $dirpath The path to the image (for example "var/storage/images/test/199-2-eng-GB").
 * @property-read string $basename The basename of the image file, without extension (for example "apple").
 * @property-read string $url Complete path + name of image file (for example "var/storage/images/test/199-2-eng-GB/apple.png").
 * @property-read string $fullPath Same as $url
 * @property-read int $timestamp A UNIX timestamp pinpointing the exact date/time when the alias was last modified.
 *                               For the "original" alias, the timestamp will reveal the time when the image was originally uploaded.
 * @property-read int $filesize The number of bytes that the image consumes.
 */
class Alias extends ValueObject
{
    /**
     * The name of the variation (for example "original").
     *
     * @var string
     */
    public $name;

    /**
     * The width as number of pixels (for example 320).
     *
     * @var int
     */
    public $width;

    /**
     * The height as number of pixels (for example 256).
     *
     * @var int
     */
    public $height;

    /**
     * FileInfo object for image alias
     *
     * @var \ezp\Io\FileInfo
     */
    public $fileInfo;

    /**
     * The alternative image text (for example "Picture of an apple.").
     *
     * @var string
     */
    public $alternativeText;

    /**
     * Contains the "alternative_text" of the original image.
     *
     * @var string
     */
    public $text;

    /**
     * The name of the original file (for example "apple.png").
     *
     * @var string
     */
    public $originalFilename;

    /**
     * A internal CRC32 value which is used when an alias is created.
     * This value is based on the filters that were used (parameters included)
     * and is checked when an alias is accessed.
     *
     * If this values differs from the configured filters (in image.ini or an override),
     * the system will recreate the alias.
     *
     * @var string
     */
    public $aliasKey;

    /**
     * DateTime object pinpointing the exact date/time when the alias was last modified.
     * For the "original" alias, the timestamp will reveal the time
     * when the image was originally uploaded.
     *
     * @var \DateTime
     */
    public $modified;

    /**
     * TRUE if the alias was created properly, that means all conversion and filters
     * were applied without problems.
     * It will be FALSE if the image manager is wrongly configured or if no
     * compatible image converters could be found.
     *
     * @var bool
     */
    public $isValid;

    /**
     * Will be set to TRUE the first time the alias is created, the next time
     * (reload of the same page) it will be FALSE.
     * It will also be set to TRUE every time the alias is re-created due to changes in filters (see alias_key).
     *
     * @var bool
     */
    public $isNew;

    /**
     * Contains extra information about the image, depending on the image type.
     * It will typically contain EXIF information from digital cameras or information about animated GIFs.
     * If there is no information, the info will be a boolean FALSE.
     *
     * @var \ezp\Base\Image\Data
     */
    public $info;

    /**
     * Generic getter.
     * Maps legacy alias information to new properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'timestamp':
                return $this->modified->getTimestamp();

            case 'mimeType':
                return $this->fileInfo->getContentType();

            case 'filename':
                return $this->fileInfo->getFilename();

            case 'suffix':
                return $this->fileInfo->getExtension();

            case 'dirpath':
                return $this->fileInfo->getPath();

            case 'basename':
                return $this->fileInfo->getBasename( $this->fileInfo->getExtension() );

            case 'fullPath':
            case 'url':
                return $this->fileInfo->getPathname();

            case 'filesize':
                return $this->fileInfo->getSize();

            default:
                throw new PropertyNotFound( $name, __CLASS__ );
        }
    }
}
