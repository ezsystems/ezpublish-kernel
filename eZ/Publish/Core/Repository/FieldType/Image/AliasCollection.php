<?php
/**
 * File containing the AliasCollection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image;
use ezp\Base\Collection\Type as TypeCollection,
    eZ\Publish\Core\Repository\FieldType\Image\Exception\InvalidAlias,
    eZ\Publish\Core\Repository\FieldType\Image\Exception\MissingClass,
    ezp\Base\BinaryRepository,
    ezp\Base\Configuration,
    ezp\Io\BinaryFile,
    ezp\Io\FileInfo,
    ezp\Io\SysInfo,
    ezp\Io\DirHandler,
    ezp\Io\FileHandler,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Content\Version;

/**
 * Image alias collection.
 * This collection can only hold image Alias objects
 */
class AliasCollection extends TypeCollection
{
    /**
     * Image type value
     *
     * @var \eZ\Publish\Core\Repository\FieldType\Image\Value
     */
    protected $imageValue;

    /**
     * @var \ezp\Base\BinaryRepository
     */
    protected $binaryRepository;

    /**
     * @var \eZ\Publish\Core\Repository\FieldType\Image\Manager
     */
    protected $imageManager;

    /**
     * @var \ezp\Base\Configuration
     */
    protected $imageConf;

    /**
     * The current serial number, the value will be 1 or higher.
     *
     * The serial number is used to create unique filenames for uploaded images,
     * it will be increased each time an image is uploaded.
     *
     * @note This was required to get around the problem where browsers
     *       caches image information, if two images were uploaded in one version (e.g. a draft)
     *       the browser would not load the new image since it thought it had not changed.
     *
     *  @var int
     */
    protected $imageSerialNumber = 0;

    public function __construct( Value $imageValue, BinaryRepository $binaryRepository, array $elements = array() )
    {
        $this->imageValue = $imageValue;
        $this->binaryRepository = $binaryRepository;
        $this->imageConf = Configuration::getInstance( 'image' );
        $this->imageManager = new Manager( $this, $binaryRepository );
        parent::__construct( 'eZ\\Publish\\Core\\Repository\\FieldType\\Image\\Alias', $elements );
    }

    /**
     * Returns image alias identified by $aliasName).
     * If needed, the alias will be created
     *
     * @param string $aliasName
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Alias
     * @throws \eZ\Publish\Core\Repository\FieldType\Image\Exception\InvalidAlias when trying to access to an invalid (not configured) image alias
     */
    public function offsetGet( $aliasName )
    {
        if ( !$this->imageManager->hasAlias( $aliasName ) )
            throw new InvalidAlias( $aliasName );

        if ( parent::offsetExists( $aliasName ) )
            return parent::offsetGet( $aliasName );

        // "original" alias is mandatory to create a new one
        if ( parent::offsetExists( 'original' ) )
            throw new MissingAlias( 'original' );

        $alias = $this->imageManager->createImageAlias( $aliasName );
        parent::offsetSet( $aliasName, $alias );
    }

    /**
     * Initializes the collection from an image path.
     * $imagePath will be considered as the original alias and will be moved to the appropriate storage directory.
     *
     * @param string $imagePath Real (absolute) path to the image.
     */
    public final function initializeFromLocalImage( $imagePath )
    {
        if ( !file_exists( $imagePath ) )
            throw new InvalidArgumentValue( 'imagePath', $imagePath, get_class() );

        $originalImageInfo = new FileInfo( $imagePath );
        $this->imageSerialNumber++;

        $destinationDir = $this->getDestinationPath( true );
        if ( !file_exists( $destinationDir ) )
            DirHandler::mkdir( $destinationDir );

        $destinationImage = $destinationDir . '/' . $this->generateTempImageName( $originalImageInfo );
        FileHandler::copy( $imagePath, $destinationImage );

        $this->createOriginalAlias( new FileInfo( $destinationImage ), $originalImageInfo );
    }

    /**
     * Creates original image alias and resets the collection with it
     *
     * @param \ezp\Io\FileInfo $imageInfo File info object for image file stored at the right place
     * @param \ezp\Io\FileInfo $originalImageInfo File info object for original image (e.g. that has been uploaded)
     */
    protected function createOriginalAlias( FileInfo $imageInfo, FileInfo $originalImageInfo )
    {
        $alias = $this->imageManager->createOriginalAlias( $imageInfo, $originalImageInfo->getBasename() );
        $alias->alternativeText = $this->imageValue->alternativeText;
        $this->imageValue->originalFilename = $originalImageInfo->getBasename();
        $this->exchangeArray( array( 'original' => $alias ) );
    }

    /**
     * Creates image alias identifed by $aliasName
     *
     * @param string $aliasName
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Alias
     * @todo Implement this method
     */
    protected function createImageAlias( $aliasName )
    {
        throw new \RuntimeException( 'Implement this method' );
    }

    /**
     * Returns path (directory) where aliases should be stored, depending on content version status and $isImageOwner
     *
     * @param bool $isImageOwner Is considered image owner a field that has created the field collection.
     *                           See {@link \eZImageAliasHandler::isImageOwner()}
     * @return string
     * @todo Implement for already published content
     * @see \eZImageAliasHandler::imagePath()
     */
    private function getDestinationPath( $isImageOwner = false )
    {
        if (
            $this->imageValue->getState( 'status' ) === Version::STATUS_PUBLISHED ||
            !$isImageOwner
        )
        {
            // @todo
            // If content already has location, $pathString should be the pathIdentificationString, starting from image.ini/FileSettings.PublishedImages
            // Else $pathString should be image.ini/FileSettings.VersionedImages
        }
        // New image for new version
        else
        {
            $pathString = $this->imageConf->get( 'FileSettings', 'TemporaryDir', 'imagetmp' );
        }

        return SysInfo::storageDirectory() . '/' . $pathString;
    }

    /**
     * Generates temporary image file name from $originalImageInfo
     *
     * @param \ezp\Io\FileInfo $originalImageInfo
     * @return string
     */
    private function generateTempImageName( FileInfo $originalImageInfo )
    {
        $fileSuffix = $originalImageInfo->getExtension();
        if ( $fileSuffix )
            $fileSuffix = '.' . $fileSuffix;

        return md5( $originalImageInfo->getBasename( $fileSuffix ) . microtime() . mt_rand() ) . $fileSuffix;
    }
}
