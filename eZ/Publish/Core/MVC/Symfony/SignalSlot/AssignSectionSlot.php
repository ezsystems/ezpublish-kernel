<?php
/**
 * File containing the Legacy\AssignSectionSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal;

/**
 * A slot handling AssignSectionSignal.
 */
class AssignSectionSlot extends AbstractSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal $signal
     */
    protected function extractContentId( Signal $signal )
    {
        return $signal->contentId;
    }

    protected function supports( Signal $signal )
    {
        return $signal instanceof AssignSectionSignal;
    }
}
