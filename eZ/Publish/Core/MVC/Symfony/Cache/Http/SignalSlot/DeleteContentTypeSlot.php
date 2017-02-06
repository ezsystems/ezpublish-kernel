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
 * A slot handling DeleteContentTypeSlot.
 */
class DeleteContentTypeSlot extends AbstractSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\ContentTypeService\DeleteContentTypeSignal;
    }

    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\DeleteContentTypeSignal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache(Signal $signal)
    {
        return $this->purgeClient->purge(['content-type-' . $signal->contentTypeId]);
    }
}
