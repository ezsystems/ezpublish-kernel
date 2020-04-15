<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Notification;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
    /** @var int */
    public $ownerId;

    /** @var string */
    public $type;

    /** @var bool */
    public $isPending;

    /** @var array */
    public $data = [];

    /** @var int */
    public $created;
}
