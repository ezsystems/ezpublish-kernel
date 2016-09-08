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
 * A slot handling PublishContentTypeDraftSignal.
 */
class PublishContentTypeSlot extends AbstractSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\ContentTypeService\PublishContentTypeDraftSignal;
    }

    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\PublishContentTypeDraftSignal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache(Signal $signal)
    {
        return $this->purgeClient->purgeByTags(['content-type-' . $signal->contentTypeDraftId]);
    }
}
