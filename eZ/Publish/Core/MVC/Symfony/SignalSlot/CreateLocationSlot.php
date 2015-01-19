<?php
/**
 * File containing the Legacy\CreateLocationSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling CreateLocationSignal.
 */
class CreateLocationSlot extends AbstractSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function supports( Signal $signal )
    {
        return $signal instanceof Signal\LocationService\CreateLocationSignal;
    }

    protected function extractContentId( Signal $signal )
    {
        return $signal->contentId;
    }
}
