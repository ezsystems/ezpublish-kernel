<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\ContentTypeValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown on create or update content type when content type is not valid.
 */
abstract class ContentTypeValidationException extends ForbiddenException
{
}
