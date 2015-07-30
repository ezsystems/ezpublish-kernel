<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Exception;

use Exception;
use RuntimeException;

/**
 * General IO exception.
 */
class IOException extends RuntimeException
{
    public function __construct($message, Exception $e = null)
    {
        parent::__construct($message, 0, $e);
    }
}
