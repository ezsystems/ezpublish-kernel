<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\User\Exception;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class UnsupportedPasswordHashType extends InvalidArgumentException
{
    public function __construct(int $hashType)
    {
        parent::__construct('hashType', "Password hash type '$hashType' is not recognized");
    }
}
