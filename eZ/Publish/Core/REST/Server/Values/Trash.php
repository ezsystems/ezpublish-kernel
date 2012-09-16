<?php
/**
 * File containing the Trash class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * Trash view model
 */
class Trash
{
    /**
     * Trash items
     *
     * @var array
     */
    public $trashItems;

    /**
     * Construct
     *
     * @param array $trashItems
     */
    public function __construct( array $trashItems )
    {
        $this->trashItems = $trashItems;
    }
}
