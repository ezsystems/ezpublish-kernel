<?php
/**
 * File containing the CreateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Location;

use eZ\Publish\SPI\Persistence\ValueObject;

class UpdateStruct extends ValueObject
{
    /**
     * Location priority
     *
     * Position of the Location among its siblings when sorted using priority
     * sort order.
     *
     * @var int
     */
    public $priority = 0;

    /**
     * Remote ID.
     *
     * A universally unique identifier.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * Specifies which property the child locations should be sorted on.
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $sortField;

    /**
     * Specifies whether the sort order should be ascending or descending.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder;
}
