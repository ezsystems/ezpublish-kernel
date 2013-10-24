<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\LimitationValidationException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\LimitationValidationException as APILimitationValidationException;

/**
 * This Exception is thrown on create, update or assign policy or role
 * when one or more given limitations are not valid
 */
class LimitationValidationException extends APILimitationValidationException
{
    /**
     * Contains an array of limitation ValidationError objects
     *
     * @var \eZ\Publish\Core\FieldType\ValidationError[]
     */
    protected $errors;

    /**
     * Generates: Limitations did not validate
     *
     * Also sets the given $errors to the internal property, retrievable by getValidationErrors()
     *
     * @param \eZ\Publish\Core\FieldType\ValidationError[] $errors
     */
    public function __construct( array $errors )
    {
        $this->validationErrors = $errors;
        parent::__construct( "Limitations did not validate" );
    }

    /**
     * Returns an array of limitation ValidationError objects
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    public function getLimitationErrors()
    {
        return $this->errors;
    }
}
