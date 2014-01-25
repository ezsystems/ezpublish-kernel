<?php
/**
 * File containing the LegacyCreateLanguageSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Slot;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
use eZContentLanguage;

/**
 * A legacy slot handling CreateLanguageSignal.
 */
class LegacyCreateLanguageSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\Repository\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\LanguageService\CreateLanguageSignal )
        {
            return;
        }

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function ()
            {
                eZContentLanguage::expireCache();
            },
            false
        );
    }
}
