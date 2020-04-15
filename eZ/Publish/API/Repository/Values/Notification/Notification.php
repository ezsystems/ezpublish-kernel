<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Notification;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a notification value.
 *
 * @property-read int $id The ID of notification
 * @property-read int $ownerId The ID of notification owner
 * @property-read bool $isPending True if notification is unreaded
 * @property-read string $type Notification type
 * @property-read \DateTimeInterface $created Creation date.
 * @property-read array $data Optional context data
 */
class Notification extends ValueObject
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $ownerId;

    /** @var bool */
    protected $isPending;

    /** @var string */
    protected $type;

    /** @var \DateTimeInterface */
    protected $created;

    /** @var array */
    protected $data = [];
}
