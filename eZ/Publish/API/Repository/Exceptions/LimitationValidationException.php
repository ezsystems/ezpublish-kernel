<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\LimitationValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown on create, update or assign policy or role
 * when one or more given limitations are not valid.
 */
abstract class LimitationValidationException extends ForbiddenException
{
    /**
     * Returns an array of limitation validation error messages.
     *
     * @return array
     */
    abstract public function getLimitationErrors();
}
