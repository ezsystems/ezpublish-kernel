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
    /** @var int $id */
    public $id;

    /** @var int $ownerId */
    public $ownerId;

    /** @var bool $isPending */
    public $isPending;

    /** @var string $type */
    public $type;

    /** @var int $created */
    public $created;

    /** @var array $data */
    public $data = [];
}
