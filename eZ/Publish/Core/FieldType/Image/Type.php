<?php
/**
 * File containing the ezimage Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\API\Repository\Values\IO\BinaryFile,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * The Image field type
 */
class Type extends FieldType
{
    /**
     * @see eZ\Publish\Core\FieldType::$validatorConfigurationSchema
     */
    protected $allowedValidators = array(
        "FileSizeValidator"
    );

    /**
     * Holds an instance of validator service
     *
     * @var \eZ\Publish\Core\Repository\ValidatorService
     */
    protected $validatorService;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\Core\Repository\ValidatorService $validatorService
     * @param FieldTypeTools $fieldTypeTools
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( ValidatorService $validatorService )
    {
        $this->validatorService = $validatorService;
    }

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $imagePath as value.
     *
     * @param string $imagePath
     * @return \eZ\Publish\Core\FieldType\Image\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $imagePath )
    {
        return new Value( $this->IOService, $imagePath );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezimage';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        $value = $this->acceptValue( $value );

        return !empty( $value->alternativeText ) ? $value->alternativeText : $value->originalFilename;
    }

    /**
     * @return \eZ\Publish\Core\FieldType\Image\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( $this->IOService );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Image\\Value',
                $inputValue
            );
        }

        if ( isset( $inputValue->path ) && !file_exists( $inputValue->path ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->path',
                'Existing fileName',
                $inputValue->path
            );
        }
        if ( isset( $inputValue->alternativeText ) && !is_string( $inputValue->alternativeText ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->alternativeText',
                'string',
                $inputValue->alternativeText
            );
        }
        if ( isset( $inputValue->fileName ) && !is_string( $inputValue->alternativeText ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->fileName',
                'string',
                $inputValue->fileName
            );
        }

        return $inputValue;
    }

    /**
     * @see \eZ\Publish\Core\FieldType::getSortInfo()
     * @return bool
     * @todo Correct?
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array(
            'alternativeText' => $value->alternativeText,
            'fileName' => $value->fileName,
            'path' => $value->path,
        );
    }

    /**
     * Converts a $value to a persistence value
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value )
    {
        // Store original data as external (to indicate they need to be stored)
        return new FieldValue(
            array(
                "data" => null,
                "externalData" => $this->toHash( $value ),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        // Restored data comes in $data, since it has already been processed
        return $this->fromHash( $fieldValue->data );
    }

}
