<?php
/**
 * File containing the FileSizeValidator class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator for checking max. size of binary files.
 *
 * @property int $maxFileSize The maximum allowed size of file, in bytes.
 */
class FileSizeValidator extends Validator
{
    protected $constraints = array(
        'maxFileSize' => false
    );

    protected $constraintsSchema = array(
        "maxFileSize" => array(
            "type" => "int",
            "default" => false
        )
    );

    public function validateConstraints( $constraints )
    {
        $validationErrors = array();

        foreach ( $constraints as $name => $value )
        {
            switch ( $name )
            {
                case "maxFileSize":
                    if ( $value !== false && !is_integer( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of integer type",
                            null,
                            array(
                                "parameter" => $name
                            )
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        array(
                            "parameter" => $name
                        )
                    );
            }
        }

        return $validationErrors;
    }

    /**
     * Checks if $value->file has the appropriate size
     *
     * @param \eZ\Publish\Core\FieldType\BinaryFile\Value $value
     *
     * @return boolean
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxFileSize'] !== false && $value->file->size > $this->constraints['maxFileSize'] )
        {
            $this->errors[] = new ValidationError(
                "The file size cannot exceed %size% byte.",
                "The file size cannot exceed %size% bytes.",
                array(
                    "size" => $this->constraints['maxFileSize']
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
