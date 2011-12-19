<?php
/**
 * File containing the BadFieldTypeInput exception
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PhpException,
    InvalidArgumentException;

/**
 * Exception thrown when the input value to a field type is not understood by
 * the field type implementation.
 */
class BadFieldTypeInput extends InvalidArgumentException implements Exception
{
    public function __construct( $value, $fieldClass = null, PhpException $previous = null )
    {
        parent::__construct(
            "The field type" . ( $fieldClass !== null ? " '{$fieldClass}'": "" ) ." did not understand the value: " . var_export( $value, true ),
            0,
            $previous
        );
    }
}
