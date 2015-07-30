<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Utils;

/**
 * Utility for logging deprecated error messages.
 */
interface DeprecationWarnerInterface
{
    /**
     * Logs a deprecation warning, as a E_USER_DEPRECATED message.
     *
     * @param string $message
     */
    public function log($message);
}
