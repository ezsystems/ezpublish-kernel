<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Notification;

use eZ\Publish\API\Repository\Values\ValueObject;

class CreateStruct extends ValueObject
{
    /** @var int */
    public $ownerId;

    /** @var string */
    public $type;

    /** @var bool */
    public $isPending = true;

    /** @var array */
    public $data = [];
}
