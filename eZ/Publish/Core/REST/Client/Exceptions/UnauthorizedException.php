<?php

/**
 * File containing the UnauthorizedException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\UnauthorizedException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
 */
class UnauthorizedException extends APIUnauthorizedException
{
}
