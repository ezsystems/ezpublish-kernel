<?php

/**
 * File containing the CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Location;

use eZ\Publish\SPI\Persistence\ValueObject;

class UpdateStruct extends ValueObject
{
    /**
     * Location priority.
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
