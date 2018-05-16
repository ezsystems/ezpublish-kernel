<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Bookmark;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
    /**
     * Name of the bookmarked location.
     *
     * @deprecated Property is here purely for BC with 5.x.
     * @var string
     */
    public $name;

    /**
     * ID of the bookmarked Location.
     *
     * @var int
     */
    public $locationId;

    /**
     * ID of bookmark owner.
     *
     * @var int
     */
    public $userId;
}
