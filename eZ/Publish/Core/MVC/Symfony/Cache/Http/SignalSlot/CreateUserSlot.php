<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling CreateUserSignal.
 */
class CreateUserSlot extends PurgeForContentHttpCacheSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserSignal $signal
     */
    protected function extractContentId(Signal $signal)
    {
        return $signal->userId;
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\UserService\CreateUserSignal;
    }
}
