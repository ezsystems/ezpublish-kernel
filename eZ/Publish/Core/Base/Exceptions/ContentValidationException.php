<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\ContentValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentValidationException as APIContentValidationException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
class ContentValidationException extends APIContentValidationException
{
}
