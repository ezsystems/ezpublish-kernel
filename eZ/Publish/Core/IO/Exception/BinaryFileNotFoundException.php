<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Exception;

use Exception;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as BaseNotFoundException;

class BinaryFileNotFoundException extends BaseNotFoundException
{
    public function __construct($path, Exception $previous = null)
    {
        parent::__construct('BinaryFile', $path, $previous);
    }
}
