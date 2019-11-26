<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionToSubtreeSignal;

class AssignSectionToSubtree extends AbstractSubtree
{
    public function receive(Signal $signal)
    {
        if (!$signal instanceof AssignSectionToSubtreeSignal) {
            return;
        }

        $this->indexSubtree($signal->locationId);
    }
}
