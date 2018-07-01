<?php

/**
 * File containing the BadStateException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\BadStateException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\BadStateException
 */
class BadStateException extends APIBadStateException
{
}
