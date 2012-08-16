<?php
/**
 * File containing the BinaryFile Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\API\Repository\FieldTypeTools,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\IOService,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\API\Repository\Values\IO\BinaryFile,
    eZ\Publish\Core\FieldType\ValidationError;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
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
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezbinaryfile";
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

        return $value->fileName;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return null
     */
    public function getEmptyValue()
    {
        return null;
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
        // null is the empty value for this type
        if ( $inputValue === null )
        {
            return $this->getEmptyValue();
        }

        // construction only from path
        if ( is_string( $inputValue ) )
        {
            $inputValue = array( 'path' => $inputValue );
        }

        // default construction from array
        if ( is_array( $inputValue ) )
        {
            $inputValue = new Value(
                $this->completeArrayData(
                    $inputValue
                )
            );
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
        if ( !isset( $inputValue->path ) || !file_exists( $inputValue->path ) )
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
                'int',
                $inputValue->fileSize
            );
        }

        return $inputValue;
    }

    /**
     * Attempts to complete the file data given in $inputData
     *
     * @param array $inputData
     * @return array
     */
    protected function completeArrayData( array $inputData )
    {
        if ( !isset( $inputData['path'] ) )
        {
            // no completion possible without path
            return $inputData;
        }

        if ( !file_exists( $inputData['path'] ) )
        {
            // no completion possible with non-existing file
            return $inputData;
        }

        if ( !isset( $inputData['fileSize'] ) )
        {
            $inputData['fileSize'] = filesize( $inputData['path'] );
        }

        if ( !isset( $inputData['fileName'] ) )
        {
            $inputData['fileName'] = basename( $inputData['path'] );
        }

        return $inputData;
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
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
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value $value
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
     * @param \eZ\Publish\Core\FieldType\BinaryFile\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $value === null )
        {
            return null;
        }

        return array(
            'fileName' => $value->fileName,
            'fileSize' => $value->fileSize,
            'path' => $value->path,
            'mimeType' => $value->mimeType,
            'downloadCount' => $value->downloadCount,
        );
    }

    /**
     * Converts a $value to a persistence value.
     *
     * In this method the field type puts the data which is stored in the field of content in the repository
     * into the property FieldValue::data. The format of $data is a primitive, an array (map) or an object, which
     * is then canonically converted to e.g. json/xml structures by future storage engines without
     * further conversions. For mapping the $data to the legacy database an appropriate Converter
     * (implementing eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter) has implemented for the field
     * type. Note: $data should only hold data which is actually stored in the field. It must not
     * hold data which is stored externally.
     *
     * The $externalData property in the FieldValue is used for storing data externally by the
     * FieldStorage interface method storeFieldData.
     *
     * The FieldValuer::sortKey is build by the field type for using by sort operations.
     *
     * @see \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @param mixed $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
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
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->externalData === null )
        {
            // empty value
            return null;
        }

        // Restored data comes in $data, since it has already been processed
        // there might be more data in the persistence value than needed here
        $result = $this->fromHash(
            array(
                'path' => ( isset( $fieldValue->externalData['path'] )
                    ? $fieldValue->externalData['path']
                    : null ),
                'fileName' => ( isset( $fieldValue->externalData['fileName'] )
                    ? $fieldValue->externalData['fileName']
                    : null ),
                'fileSize' => ( isset( $fieldValue->externalData['fileSize'] )
                    ? $fieldValue->externalData['fileSize']
                    : null ),
                'downloadCount' => ( isset( $fieldValue->externalData['downloadCount'] )
                    ? $fieldValue->externalData['downloadCount']
                    : null ),
            )
        );
        return $result;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
