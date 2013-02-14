<?php
/**
 * File containing the ParameterNotFoundException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Exception;

/**
 * This exception is thrown when a dynamic parameter could not be found in any scope.
 */
class ParameterNotFoundException extends \InvalidArgumentException
{
    public function __construct( $paramName, $namespace, array $triedScopes = array() )
    {
        $this->message = "Parameter '$paramName' with namespace '$namespace' could not be found.";
        if ( !empty( $triedScopes ) )
            $this->message .= " Tried scopes: " . implode( ', ', $triedScopes );
    }
}
