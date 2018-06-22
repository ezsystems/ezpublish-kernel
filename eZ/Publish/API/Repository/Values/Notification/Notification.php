<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Notification;

use eZ\Publish\API\Repository\Values\ValueObject;

class Notification extends ValueObject
{
    /** @var int $id */
    protected $id;

    /** @var int $ownerId */
    protected $ownerId;

    /** @var bool $isPending */
    protected $isPending;

    /** @var string $type */
    protected $type;

    /** @var \DateTimeInterface $created */
    protected $created;

    /** @var array $data */
    protected $data = [];
}
