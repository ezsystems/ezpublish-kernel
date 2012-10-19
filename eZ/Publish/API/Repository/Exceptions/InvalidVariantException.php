<?php
/**
 * File containing the InvalidVariantException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

class InvalidVariantException extends InvalidArgumentException
{
    public function __construct( $variantName, $variantType, $code = 0, Exception $previous = null )
    {
        parent::__construct( "Invalid variant '$variantName' for $variantType", $code, $previous );
    }
}
