<?php
/**
 * File containing the Solr\Slot\CreateUserGroup class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Solr\Slot;

/**
 * A Solr slot handling CreateUserGroupSignal.
 */
class CreateUserGroup extends Slot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\UserService\CreateUserGroupSignal )
            return;

        $userGroupContentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo( $signal->userGroupId );

        $this->searchHandler->contentSearchHandler()->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $userGroupContentInfo->id,
                $userGroupContentInfo->currentVersionNo
            )
        );

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent( $userGroupContentInfo->id );
        foreach ( $locations as $location )
        {
            $this->searchHandler->locationSearchHandler()->indexLocation( $location );
        }
    }
}
