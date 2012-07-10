<?php
/**
 * File containing the FileSizeValidator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\Validator,
    eZ\Publish\Core\FieldType\Value as BaseValue;

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

    /**
     * Checks if $value->file has the appropriate size
     *
     * @param \eZ\Publish\Core\FieldType\BinaryFile\Value $value
     *
     * @return bool
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxFileSize'] !== false && $value->file->size > $this->constraints['maxFileSize'] )
        {
            $this->errors[] = "The file size can not exceed {$this->constraints['maxFileSize']} bytes.";
            $isValid = false;
        }

        return $isValid;
    }
}
