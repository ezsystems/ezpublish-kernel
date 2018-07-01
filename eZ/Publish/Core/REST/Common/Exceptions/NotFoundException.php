<?php

/**
 * File containing the NotFoundException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\NotFoundException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\NotFoundException
 */
class NotFoundException extends APINotFoundException
{
}
