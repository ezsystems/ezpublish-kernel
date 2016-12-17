<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Exception;

use Exception;

class InvalidBinaryPrefixException extends InvalidBinaryFileIdException
{
    public function __construct($id, $prefix, $code = 0)
    {
        $this->setMessageTemplate("Argument 'BinaryFile::id' is invalid: '%id%' is wrong value, it does not contain prefix '%prefix%'. Is 'var_dir' config correct?");
        $this->setParameters(['%id%' => $id, '%prefix%' => $prefix]);

        // Parent does not let us set specifc message, so we jump all the way up to root Exception __construct().
        Exception::__construct($this->getBaseTranslation(), $code);
    }
}
