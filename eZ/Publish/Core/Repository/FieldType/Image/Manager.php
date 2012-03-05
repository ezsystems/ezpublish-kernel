<?php
/**
 * File containing the image Manager class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image;
use ezp\Base\Legacy\Carpet,
    ezp\Base\Image\Data as ImageData,
    ezp\Base\BinaryRepository,
    eZ\Publish\Core\Repository\FieldType\Image\AliasCollection,
    ezp\Io\FileInfo,
    DateTime;

/**
 * Wraps eZImageManager class from old eZ Publish
 *
 * @note This implementation is to be changed not to be dependent on the old eZImageManager
 */
class Manager extends Carpet
{
    protected static $className = 'eZImageManager';

    /**
     * Image alias collection object for which this manager is used
     *
     * @var \eZ\Publish\Core\Repository\FieldType\Image\AliasCollection
     */
    protected $aliasCollection;

    /**
     * The binary repository.
     * Can be used to store image aliases once generation is done
     *
     * @var \ezp\Base\BinaryRepository
     */
    protected $binaryRepository;

    /**
     * Legacy image manager class
     *
     * @var \eZImageManager
     */
    protected $object;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Image\AliasCollection $aliasCollection The image alias collection calling this manager
     * @param \ezp\Base\BinaryRepository The binary repository
     */
    public function __construct( AliasCollection $aliasCollection, BinaryRepository $binaryRepository )
    {
        parent::__construct( static::$className );
        $this->aliasCollection = $aliasCollection;
        // Build the legacy eZImageManager object through eZImageManager::factory()
        $this->object = forward_static_call( array( static::$className, 'factory' ) );
    }

    /**
     * Creates the image alias $aliasName if it's not already part of the
     * existing aliases
     *
     * @param string $aliasName Name of the alias to create
     * @param array $parameters Optional array that can be used to specify the original image's basename
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Alias
     */
    public function createImageAlias( $aliasName )
    {
        $alias = new Alias;

        return $alias;
    }

    /**
     * Creates original alias from $imageInfo.
     * $originalFilename will be stored in the alias object
     *
     * @param \ezp\Io\FileInfo $imageInfo Object containing all information regarding physical image file
     * @param string $originalFilename The original file name to store in the alias ($imageInfo filename is a temporary unique hash)
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Alias
     */
    public function createOriginalAlias( FileInfo $imageInfo, $originalFilename )
    {
        // Store in the repository as operations will be done in the repository for concurrency protection.
        // @see eZImageManager::createImageAlias(), starting from line 864
        $imageFile = $this->binaryRepository->createFromLocalFile( $imageInfo->getPathname() );

        $aliasListForLegacy = array(
            'original' => array(
                'name' => (string)$imageInfo,
                'url' => $imageInfo->getPathname(),
                'filename' => $imageInfo->getFilename(),
                'dirpath' => $imageInfo->getPath(),
                'basename' => $imageInfo->getBasename( $imageInfo->getExtension() ),
                'suffix' => $imageInfo->getExtension(),
                'prefix' => false,
                'is_valid' => true,
                'alternative_text' => '',
                'original_file_name' => $originalFilename
            )
        );

        // $aliasListForLegacy is passed by reference and will be filled by legacy image manager
        $this->object->createImageAlias(
            'original',
            $aliasListForLegacy,
            array( 'basename' => $aliasListForLegacy['original']['basename'] )
        );

        // Refetch the image file from the binary repository
        // Modification could have been made in underlying image manager
        $imageFile = $this->binaryRepository->load( $imageInfo->getPathname() );

        // Image analysis, for advanced information like EXIF and GIF info
        // Result will be contained in $aliasListForLegacy['original']['info']
        $this->object->analyzeImage( $aliasListForLegacy['original'] );

        $width = isset( $aliasListForLegacy['original']['width'] ) ? $aliasListForLegacy['original']['width'] : false;
        $height = isset( $aliasListForLegacy['original']['height'] ) ? $aliasListForLegacy['original']['height'] : false;
        $alias = new Alias(
            array(
                'name' => 'original',
                'width' => $width,
                'height' => $height,
                'fileInfo' => new FileInfo( $aliasListForLegacy['original']['url'] ),
                'originalFilename' => $originalFilename,
                'aliasKey' => $aliasListForLegacy['original']['alias_key'],
                'modified' => new DateTime,
                'isValid' => true,
                'isNew' => true,
            )
        );
        if ( isset( $aliasListForLegacy['original']['info'] ) && $aliasListForLegacy['original']['info'] !== false )
            $alias->info = $this->buildImageData( $aliasListForLegacy['original']['info'] );

        return $alias;
    }

    /**
     * Builds an {@link \ezp\Base\Image\Data} object from $imageInfo and maps properties
     *
     * @param array $imageInfo Image info array returned by legacy image analyzer
     * @see \eZImageManager::analyzeImage()
     * @return \ezp\Base\Image\Data
     */
    private function buildImageData( array $imageInfo )
    {
        $imageData = new ImageData;
        foreach ( $imageInfo as $imageProp => $value )
        {
            switch ( $imageProp )
            {
                case 'exif':
                case 'ifd0':
                    $imageData->exif[$imageProp] = $value;
                    break;

                case 'Width':
                case 'width':
                    $imageData->width = (int)$value;
                    break;

                case 'Height':
                case 'height':
                    $imageData->height = (int)$value;
                    break;

                case 'IsColor':
                    $imageData->isColor = (bool)$value;
                    break;

                default:
                    // Check if camelized property is available
                    $camelizedProp = str_replace( '_', ' ', $imageProp );
                    $camelizedProp = ucwords( $camelizedProp );
                    $camelizedProp = lcfirst( str_replace( ' ', '', $camelizedProp ) );
                    if ( property_exists( $imageData, $camelizedProp ) )
                        $imageData->$camelizedProp = $value;
                    else
                        $imageData->advancedData[$imageData] = $value;
            }
        }

        return $imageData;
    }
}
