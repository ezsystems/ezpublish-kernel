<?php
/**
 * File containing the InvalidArgumentExceptionStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Exceptions;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

/**
 *
 * This exception is throen if a service method is called with an illegal or non appriprite value
 *
 */
class InvalidArgumentExceptionStub extends InvalidArgumentException
{
    /**
     * Returns an additional error code which indicates why an action could not be performed
     *
     * @return int An error code
     */
    function getErrorCode()
    {
        // TODO: Implement getErrorCode() method.
    }

}
