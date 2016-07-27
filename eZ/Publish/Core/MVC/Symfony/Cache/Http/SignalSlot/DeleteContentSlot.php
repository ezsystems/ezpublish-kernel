<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling DeleteContentSignal.
 *
 * @todo Change to clear precise cache items on content deletion (implies changes to how this signal is used and emitted).
 */
class DeleteContentSlot extends PurgeAllHttpCacheSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\ContentService\DeleteContentSignal;
    }
}
