<?php

/**
 * File containing the Parser Exception class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Exceptions;

use InvalidArgumentException as PHPInvalidArgumentException;

/**
 * Exception thrown if a parser discovers an error.
 */
class Parser extends PHPInvalidArgumentException
{
}
