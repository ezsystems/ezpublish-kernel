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
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $IOService;

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
    public function __construct( ValidatorService $validatorService, FieldTypeTools $fieldTypeTools, Repository $repository )
    {
        parent::__construct( $validatorService, $fieldTypeTools );
        $this->IOService = $repository->getIOService();
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
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Image\\Value',
                $inputValue
            );
        }

        if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
        {
            throw new InvalidArgumentType(
                '$inputValue->file',
                'eZ\Publish\API\Repository\Values\IO\BinaryFile',
                $inputValue->file
            );
        }

        return $inputValue;
    }

    /**
     * @see \eZ\Publish\Core\FieldType::getSortInfo()
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
     * @return \eZ\Publish\Core\FieldType\Image\Value $value
     */
    public function fromHash( $hash )
    {
        throw new \Exception( "Not implemented yet" );
        return new Value( $this->IOService, $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Image\Value $value
     *
     * @return mixed
     */
    public function toHash( Value $value )
    {
        throw new \Exception( "Not implemented yet" );
        return $value->value;
    }
}
