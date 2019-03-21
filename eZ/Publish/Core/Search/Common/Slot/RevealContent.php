<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;

class RevealContent extends AbstractSubtree
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\RevealContentSignal) {
            return;
        }

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($signal->contentId);
        foreach ($locations as $location) {
            $this->indexSubtree($location->id);
        }
    }
}
