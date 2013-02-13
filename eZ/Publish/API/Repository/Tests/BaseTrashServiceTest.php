<?php
/**
 * File containing the BaseTrashServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
        // remoteId of the "Media" page main location
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Media" page location
        $mediaLocation = $locationService->loadLocationByRemoteId(
            $mediaRemoteId
        );

        // Trash the "Community" page location
        $trashItem = $trashService->trash( $mediaLocation );
        /* END: Inline */

        return $trashItem;
    }
}
