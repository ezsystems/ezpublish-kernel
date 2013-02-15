<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException as APIContentTypeFieldDefinitionValidationException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
class ContentTypeFieldDefinitionValidationException extends APIContentTypeFieldDefinitionValidationException
{
    /**
     * Contains an array of field ValidationError objects indexed with FieldDefinition id and language code
     *
     * Example:
     * <code>
     *  $fieldErrors = $exception->getFieldErrors();
     *  $fieldErrors["43"]["eng-GB"]->getTranslatableMessage();
     * </code>
     *
     * @var \eZ\Publish\Core\FieldType\ValidationError[]
     */
    protected $errors;

    /**
     * Generates: Content fields did not validate
     *
     * Also sets the given $fieldErrors to the internal property, retrievable by getFieldErrors()
     *
     * @param \eZ\Publish\Core\FieldType\ValidationError[] $errors
     */
    public function __construct( array $errors )
    {
        // var_dump( $errors );
        $this->errors = $errors;
        parent::__construct( "ContentType FieldDefinitions did not validate" );
    }

    /**
     * Returns an array of field validation error messages
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    public function getFieldErrors()
    {
        return $this->errors;
    }
}
