<?php
/**
 * File containing the BaseTrashServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Base class for trash specific tests.
 */
abstract class BaseTrashServiceTest extends BaseTest
{
    /**
     * Creates a trashed item from the <b>Community</b> page location and stores
     * this item in a location variable named <b>$trashItem</b>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    protected function createTrashItem()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // remoteId of the "Community" page main location
        $communityRemoteId = 'c4604fb2e100a6681a4f53fbe6e5eeae';

        $trashService    = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Community" page location
        $communityLocation = $locationService->loadLocationByRemoteId(
            $communityRemoteId
        );

        // Trash the "Community" page location
        $trashItem = $trashService->trash( $communityLocation );
        /* END: Inline */

        return $trashItem;
    }
}
