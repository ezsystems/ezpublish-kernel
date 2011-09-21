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
 */
class FileSizeValidator extends Validator
{
    /**
     * The maximum allowed size of file, in bytes.
     *
     * @var int
     */
    public $maxFileSize;

    /**
     * Returns the name of the validator.
     *
     * @return string
     */
    public function name()
    {
        return 'FileSizeValidator';
    }

    /**
     * Checks if $binaryFile has the appropriate size
     *
     * @param \ezp\Io\BinaryFile $binaryFile
     * @return bool
     */
    public function validate( $binaryFile )
    {
        $isValid = true;

        if ( $this->maxFileSize !== null && $binaryFile->size > $this->maxFileSize )
        {
            $this->errors[] = "The file size can not exceed {$this->maxFileSize} btes.";
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Combines configurable constraints in the validator and creates a map.
     *
     * This map is then supposed to be used inside a FieldDefinition.
     *
     * @internal
     * @return array
     */
    public function getValidatorConstraints()
    {
        return array(
            $this->name() => array(
                'maxFileSize' => $this->maxFileSize,
            ));
    }

}
