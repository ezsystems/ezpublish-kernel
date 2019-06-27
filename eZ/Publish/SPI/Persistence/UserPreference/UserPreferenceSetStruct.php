<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\UserPreference;

use eZ\Publish\SPI\Persistence\ValueObject;

class UserPreferenceSetStruct extends ValueObject
{
    /** @var int */
    public $userId;

    /** @var string */
    public $name;

    /** @var string */
    public $value;
}
