<?php
/**
 * File containing the image Manager class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image;
use eZ\Publish\Core\Repository\FieldType\Image\Data as ImageData,
    eZ\Publish\API\Repository\IOService,
    eZ\Publish\Core\Repository\FieldType\Image\AliasCollection,
    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    splFileInfo,
    DateTime,
    ReflectionClass;

/**
 * Wraps eZImageManager class from old eZ Publish
 *
 * @todo This implementation is to be changed not to be dependent on the old eZImageManager
 * @todo Rewrite image fieldtype
 */
class Manager
{
    protected static $className = 'eZImageManager';

    /**
     * Image alias collection object for which this manager is used
     *
     * @var \eZ\Publish\Core\Repository\FieldType\Image\AliasCollection
     */
    protected $aliasCollection;

    /**
     * The IO service.
     * Can be used to store image aliases once generation is done
     *
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $IOService;

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
     * @param \eZ\Publish\API\Repository\IOService $IOService The IO Service
     */
    public function __construct( AliasCollection $aliasCollection, IOService $IOService )
    {
        $this->aliasCollection = $aliasCollection;
        $this->IOService = $IOService;
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
     * @param \splFileInfo $imageInfo Object containing all information regarding physical image file
     * @param string $originalFilename The original file name to store in the alias ($imageInfo filename is a temporary unique hash)
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Alias
     */
    public function createOriginalAlias( splFileInfo $imageInfo, $originalFilename )
    {
        // Store in the repository as operations will be done in the repository for concurrency protection.
        // @see eZImageManager::createImageAlias(), starting from line 864
        $createStruct = $this->IOService->newBinaryCreateStructFromLocalFile( $imageInfo->getPathname() );
        $imageFile = $this->IOService->createBinaryFile( $createStruct );

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
        $imageFile = $this->IOService->loadBinaryFile( $imageInfo->getPathname() );

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
                'fileInfo' => new splFileInfo( $aliasListForLegacy['original']['url'] ),
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
     * Builds an ImageData object from $imageInfo and maps properties
     *
     * @param array $imageInfo Image info array returned by legacy image analyzer
     * @see \eZImageManager::analyzeImage()
     * @return \eZ\Publish\Core\Repository\FieldType\Image\Data
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

    /**
     * "Lifts the carpet and sweeps the dust under it"
     * In other more pragmatic words, Instantiates the object to be abstracted.
     *
     * Note: This method makes use of Reflection if $constructorArgs contains more than 1 element.
     * Therefore, to avoid too much performance cost, please consider extending this class
     * and reimplement this method in order to pass the exact number of arguments
     * to the abstracted class's constructor
     *
     * @param array|null $constructorArgs Arguments to pass to the constructor.
     *                                    Set to null (default) if no argument is required
     * @return Manager
     *
     * @todo Fix inclusion of class files !
     */
    public function lift( array $constructorArgs = null )
    {
        $className = self::$className;
        if ( $constructorArgs === null )
        {
            $this->object = new $className;
        }
        else if ( isset( $constructorArgs[0] ) && !isset( $constructorArgs[1] ) )
        {
            $this->object = new $className( $constructorArgs[0] );
        }
        else
        {
            $refClass = new ReflectionClass( $className );
            $this->object = $refClass->newInstanceArgs( $constructorArgs );
        }

        return $this;
    }

    /**
     * Access to abstracted object's property, identified by $name.
     *
     * @param string $name Property name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     * @return mixed
     */
    public function __get( $name )
    {
        if ( !property_exists( $this->object, $name ) )
            throw new PropertyNotFoundException( $name, static::$className );

        return $this->object->$name;
    }

    /**
     * Sets $value to abstracted object's property, identified by $name
     *
     * @param string $name Property name
     * @param mixed $value Value to set
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function __set( $name, $value )
    {
        if ( !property_exists( $this->object, $name ) )
            throw new PropertyNotFoundException( $name, static::$className );

        $this->object->$name = $value;
    }

    /**
     * Calls $method with $arguments on abstracted object
     *
     * @param string $method Method name
     * @param array $arguments
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @return mixed
     */
    public function __call( $method, array $arguments )
    {
        if ( !method_exists( $this->object, $method ) )
            throw new InvalidArgumentException( $method, " does not exist on " . static::$className );

        return call_user_func_array( array( $this->object, $method ), $arguments );
    }

    /**
     * Calls static $method with $arguments on abstracted object class
     *
     * @param string $method Method name
     * @param array $arguments
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @return mixed
     */
    public static function __callStatic( $method, array $arguments )
    {
        if ( !method_exists( static::$className, $method ) )
            throw new InvalidArgumentException( $method, " does not exist on " . static::$className );

        return forward_static_call_array( array( static::$className, $method ), $arguments );
    }
}
