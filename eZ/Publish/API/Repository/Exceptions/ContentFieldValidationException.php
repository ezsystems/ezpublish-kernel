<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid.
 */
abstract class ContentFieldValidationException extends ForbiddenException
{
    /**
     * Returns an array of field validation error messages.
     *
     * @return array
     */
    abstract public function getFieldErrors();
}
