<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Image Field input value.
 * Used to provide a new image file for input, using $inputUri
 */
class InputValue extends BaseValue
{
    /**
     * The URI to the image file to be used as a source.
     * Can only be set through the constructor or fromString.
     * @var string
     */
    protected $inputUri;

    /**
     * @param array $imageData Keys: inputUri, alternativeText, fileName.
     * @throws InvalidArgumentException If inputUri key is missing or not a valid image file
     */
    public function __construct( array $imageData = array() )
    {
        if ( !isset( $imageData['inputUri'] ) )
        {
            throw new InvalidArgumentException( 'imageData[inputUri]', "Missing argument" );
        }

        if ( !file_exists( $imageData['inputUri'] ) )
        {
            throw new InvalidArgumentException( 'imageData[inputUri]', "File not found" );
        }

        if ( !getimagesize( $imageData['inputUri'] ) )
        {
            throw new InvalidArgumentException( 'imageData[inputUri]', "Not a valid image" );
        }

        // set image size from provided valid file
        $imageData['fileSize'] = filesize( $imageData['inputUri'] );

        parent::__construct( $imageData );
    }

    /**
     * Creates a value only from a file path
     *
     * @param string $path Path to a local file
     *
     * @throws InvalidArgumentType if $path doesn't refer to an existing file
     * @return Value
     */
    public static function fromString( $path )
    {
        return new static(
            array(
                'inputUri' => $path,
                'fileName' => basename( $path )
            )
        );
    }
}
