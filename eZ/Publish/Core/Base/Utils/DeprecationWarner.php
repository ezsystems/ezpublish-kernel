<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Utils;

class DeprecationWarner implements DeprecationWarnerInterface
{
    public function log($message)
    {
        @trigger_error($message, E_USER_DEPRECATED);
    }
}
