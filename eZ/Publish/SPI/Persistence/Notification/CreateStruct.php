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
    /** @var int $ownerId */
    public $ownerId;

    /** @var string $type */
    public $type;

    /** @var bool $isPending */
    public $isPending;

    /** @var array $data */
    public $data = [];

    /** @var int $created */
    public $created;
}
