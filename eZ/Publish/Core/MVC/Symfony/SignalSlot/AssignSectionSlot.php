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
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\SectionService\AssignSectionSignal )
        {
            return;
        }

        $this->httpCacheClearer->purge( $this->getLocationId( $signal->contentId ) );
    }

    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal $signal
     */
    protected function extractContentId( Signal $signal )
    {
        return $signal->contentId;
    }

    /**
     * Checks if $signal is supported by this handler
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return bool
     */
    protected function supports( Signal $signal )
    {
        return $signal instanceof AssignSectionSignal;
    }
}
