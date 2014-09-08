<?php
/**
 * File containing the Legacy\AssignSectionSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZContentObject;
use eZSearch;

/**
 * A legacy slot handling AssignSectionSignal.
 */
class LegacyAssignSectionSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\SectionService\AssignSectionSignal )
            return;// @todo Error Logging? No exception seem to be defined for this case

        $this->runLegacyKernelCallback(
            function () use ( $signal )
            {
                eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                eZSearch::updateObjectsSection( array( $signal->contentId ), $signal->sectionId );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            }
        );
    }
}
