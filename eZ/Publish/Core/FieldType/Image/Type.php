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
    eZ\Publish\Core\FieldType\FileService,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The Image field type
 */
class Type extends FieldType
{
    /**
     * @see eZ\Publish\Core\FieldType::$validatorConfigurationSchema
     */
    protected $validatorConfigurationSchema = array(
        "FileSizeValidator" => array(
            'maxFileSize' => array(
                'type' => 'int',
                'default' => false,
            )
        )
    );

    /**
     * File service
     *
     * @var FileService
     */
    protected $fileService;

    /**
     * Creates a new Image FieldType
     *
     * @param FileService $fileService
     */
    public function __construct( FileService $fileService )
    {
        $this->fileService = $fileService;
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
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        // default construction from array
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        // just given the file path as a string
        else if ( is_string( $inputValue ) )
        {
            $inputValue = Value::fromString( $inputValue );
        }

        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Image\\Value',
                $inputValue
            );
        }

        // Required paramater $path
        if ( !isset( $inputValue->path ) || !$this->fileExists( $inputValue->path ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->path',
                'Existing fileName',
                $inputValue->path
            );
        }
        // Required parameter $fileName
        if ( !isset( $inputValue->fileName ) || !is_string( $inputValue->fileName ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->fileName',
                'string',
                $inputValue->fileName
            );
        }

        // Required parameter $fileSize
        if ( !isset( $inputValue->fileSize ) || !is_int( $inputValue->fileSize ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->fileSize',
                'string',
                $inputValue->fileSize
            );
        }

        // Optional parameter $alternativeText
        if ( isset( $inputValue->alternativeText ) && !is_string( $inputValue->alternativeText ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->alternativeText',
                'string',
                $inputValue->alternativeText
            );
        }

        return $inputValue;
    }

    /**
     * Returns if the given $value is considered empty by the field type
     *
     * @param mixed $value
     * @return bool
     */
    public function isEmptyValue( $value )
    {
        return $value === null || $value->fileName === null;
    }

    /**
     * Returns if the given $path exists on the local disc or in the file
     * storage
     *
     * @param string $path
     * @return bool
     */
    protected function fileExists( $path )
    {
        return (
            ( substr( $path, 0, 1 ) === '/' && file_exists( $path ) )
            || $this->fileService->exists( $path )
        );
    }

    /**
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $errors = array();
        foreach ( (array)$fieldDefinition->getValidatorConfiguration() as $validatorIdentifier => $parameters )
        {
            switch ( $validatorIdentifier )
            {
                case 'FileSizeValidator':
                    if ( !isset( $parameters['maxFileSize'] ) || $parameters['maxFileSize'] == false )
                    {
                        // No file size limit
                        break;
                    }
                    // Database stores maxFileSize in MB
                    if ( $fieldValue !== null && ( $parameters['maxFileSize'] * 1024 * 1024 ) < $fieldValue->fileSize )
                    {
                        $errors[] = new ValidationError(
                            "The file size cannot exceed %size% byte.",
                            "The file size cannot exceed %size% bytes.",
                            array(
                                "size" => $parameters['maxFileSize'],
                            )
                        );
                    }
                    break;
            }
        }
        return $errors;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration( $validatorConfiguration )
    {
        $validationErrors = array();

        foreach ( $validatorConfiguration as $validatorIdentifier => $parameters )
        {
            switch ( $validatorIdentifier )
            {
                case 'FileSizeValidator':
                    if ( !isset( $parameters['maxFileSize'] ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator %validator% expects parameter %parameter% to be set.",
                            null,
                            array(
                                "validator" => $validatorIdentifier,
                                "parameter" => 'maxFileSize',
                            )
                        );
                        break;
                    }
                    if ( !is_int( $parameters['maxFileSize'] ) && !is_bool( $parameters['maxFileSize'] ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator %validator% expects parameter %parameter% to be of %type%.",
                            null,
                            array(
                                "validator" => $validatorIdentifier,
                                "parameter" => 'maxFileSize',
                                "type" => 'integer',
                            )
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator '%validator%' is unknown",
                        null,
                        array(
                            "validator" => $validatorIdentifier
                        )
                    );
            }
        }

        return $validationErrors;
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
        if( $hash === null )
        {
            // empty value
            return null;
        }

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
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }

        return array(
            'alternativeText' => $value->alternativeText,
            'fileName' => $value->fileName,
            'fileSize' => $value->fileSize,
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
        if ( $fieldValue->data === null )
        {
            // empty value
            return null;
        }

        // Restored data comes in $data, since it has already been processed
        // there might be more data in the persistence value than needed here
        $result = $this->fromHash(
            array(
                'alternativeText' => ( isset( $fieldValue->data['alternativeText'] )
                    ? $fieldValue->data['alternativeText']
                    : null ),
                'fileName' => ( isset( $fieldValue->data['fileName'] )
                    ? $fieldValue->data['fileName']
                    : null ),
                'fileSize' => ( isset( $fieldValue->data['fileSize'] )
                    ? $fieldValue->data['fileSize']
                    : null ),
                'path' => ( isset( $fieldValue->data['path'] )
                    ? $fieldValue->data['path']
                    : null ),
            )
        );
        return $result;
    }

}
