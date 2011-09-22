<?php
/**
 * File containing the FieldValidation exception
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    \Exception as PhpException,
    \InvalidArgumentException;

/**
 * Exception thrown when the input value to a field type is not understood by
 * the field type implementation.
 */
class FieldValidation extends InvalidArgumentException implements Exception
{
    public $validationErrors = array();

    /**
     * Constructs a FieldValidation exception
     * @param string $what What did not validate ?
     * @param string|array $validationErrors The validation error(s) that occurred
     * @param \Exception $previous
     */
    public function __construct( $what, $validationErrors, PhpException $previous = null )
    {
        if( !is_array( $validationErrors ) )
            $validationErrors = array( $validationErrors );
        $this->validationErrors = $validationErrors;

        parent::__construct(
            "The field {$what} did not validate. Validation errors: " . var_export( $validationErrors, true ),
            0,
            $previous
        );
    }
}
