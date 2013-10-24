<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\LimitationValidationException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown on create, update or assign policy or role
 * when one or more given limitations are not valid
 */
abstract class LimitationValidationException extends ForbiddenException
{
    /**
     * Returns an array of limitation validation error messages
     *
     * @return array
     */
    abstract public function getLimitationErrors();
}
