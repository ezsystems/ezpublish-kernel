<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

/**
 * A Search Engine slot handling CreateUserSignal.
 */
class CreateUser extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\UserService\CreateUserSignal )
            return;

        $userContentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo( $signal->userId );

        $this->searchHandler->contentSearchHandler()->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $userContentInfo->id,
                $userContentInfo->currentVersionNo
            )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent( $userContentInfo->id );
        foreach ( $locations as $location )
        {
            $this->searchHandler->locationSearchHandler()->indexLocation( $location );
        }
    }
}
