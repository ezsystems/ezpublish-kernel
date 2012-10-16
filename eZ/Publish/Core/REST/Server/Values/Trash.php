<?php
/**
 * File containing the Trash class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Trash view model
 */
class Trash extends RestValue
{
    /**
     * Trash items
     *
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem[]
     */
    public $trashItems;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem[] $trashItems
     */
    public function __construct( array $trashItems )
    {
        $this->trashItems = $trashItems;
    }
}
