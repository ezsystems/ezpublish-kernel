<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown on create or update content type one or more given field definitions are not valid.
 */
abstract class ContentTypeFieldDefinitionValidationException extends ForbiddenException
{
    /**
     * Returns an array of field definition validation error messages.
     *
     * @return array
     */
    abstract public function getFieldErrors();
}
