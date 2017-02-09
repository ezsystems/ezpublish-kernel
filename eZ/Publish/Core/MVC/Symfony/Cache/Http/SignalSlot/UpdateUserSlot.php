<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling UpdateUserSignal.
 *
 * @deprecated since 6.8. The platform-http-cache package defines slots for http-cache multi-tagging.
 */
class UpdateUserSlot extends PurgeForContentHttpCacheSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserSignal $signal
     */
    protected function extractContentId(Signal $signal)
    {
        return $signal->userId;
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\UserService\UpdateUserSignal;
    }
}
