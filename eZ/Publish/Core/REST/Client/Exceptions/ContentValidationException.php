<?php

/**
 * File containing the ContentValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentValidationException as APIContentValidationException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\ContentValidationException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\ContentValidationException
 */
class ContentValidationException extends APIContentValidationException
{
}
