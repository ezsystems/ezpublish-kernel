<?php
/**
 * File containing the FileSizeValidator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Validator;
use ezp\Content\FieldType\Validator;

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
     * Checks if $binaryFile has the appropriate size
     *
     * @param \ezp\Io\BinaryFile $binaryFile
     * @return bool
     */
    public function validate( $binaryFile )
    {
        $isValid = true;

        if ( $this->constraints['maxFileSize'] !== false && $binaryFile->size > $this->constraints['maxFileSize'] )
        {
            $this->errors[] = "The file size can not exceed {$this->constraints['maxFileSize']} btes.";
            $isValid = false;
        }

        return $isValid;
    }
}
