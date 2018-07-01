<?php

/**
 * File containing the MethodNotAllowedException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use BadMethodCallException;

/**
 * Exception thrown if an unsupported method is called.
 */
class MethodNotAllowedException extends BadMethodCallException
{
}
