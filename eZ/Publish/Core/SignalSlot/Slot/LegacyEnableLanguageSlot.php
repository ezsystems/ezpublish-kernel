<?php
/**
 * File containing the LegacyEnableLanguageSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentLanguage;

/**
 * A legacy slot handling EnableLanguageSignal.
 */
class LegacyEnableLanguageSlot extends AbstractLegacySlot
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
        if ( !$signal instanceof Signal\LanguageService\EnableLanguageSignal )
        {
            return;
        }

        $this->runLegacyKernelCallback(
            function ()
            {
                eZContentLanguage::expireCache();
            }
        );
    }
}
