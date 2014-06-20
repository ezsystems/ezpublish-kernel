<?php
/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\API\Repository\Exceptions\NotFound;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * This Exception is thrown if repository did not find the limitation.
 *
 * @package eZ\Publish\API\Repository\Exceptions
 */
abstract class LimitationNotFoundException extends NotFoundException
{
}
