<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException as APIContentFieldValidationException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
class ContentFieldValidationException extends APIContentFieldValidationException
{
    /**
     * @todo error messages should be translatable
     * Contains an array of field validation error messages indexed with FieldDefinition id and language code
     *
     * Example:
     * <code>
     * protected $fieldErrors = array(
     *     "48" => array(
     *         "eng-GB" => array(
     *             "The file size can not exceed 4096 bytes."
     *         )
     *     ),
     *    "256" => array(
     *         "eng-US" => array(
     *             "The string can not be shorter than 64 characters."
     *         )
     *     )
     * );
     * </code>
     *
     * @var array
     */
    protected $errors;

    /**
     * Generates: Content fields did not validate
     *
     * Also sets the given $fieldErrors to the internal property, retrievable by getFieldErrors()
     *
     * @param array $errors
     */
    public function __construct( array $errors )
    {
        $this->errors = $errors;
        parent::__construct( "Content fields did not validate" );
    }

    /**
     * Returns an array of field validation error messages
     *
     * @return array
     */
    public function getFieldErrors()
    {
        return $this->errors;
    }
}
