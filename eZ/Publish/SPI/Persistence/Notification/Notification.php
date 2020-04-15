<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Notification;

use eZ\Publish\SPI\Persistence\ValueObject;

class Notification extends ValueObject
{
    /** @var int */
    public $id;

    /** @var int */
    public $ownerId;

    /** @var bool */
    public $isPending;

    /** @var string */
    public $type;

    /** @var int */
    public $created;

    /** @var array */
    public $data = [];
}
