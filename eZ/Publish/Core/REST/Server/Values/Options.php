<?php
/**
 * File containing the CreatedPolicy class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a resource OPTIONS response
 */
class Options extends ValueObject
{
    /**
     * The methods allowed my the resource
     *
     * @var array
     */
    public $allowedMethods;

    public function __construct( $allowedMethods )
    {
        $this->allowedMethods = $allowedMethods;
    }
}
