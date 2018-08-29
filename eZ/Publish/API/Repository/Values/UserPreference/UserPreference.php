<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\UserPreference;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a user preference value.
 *
 * @property-read string $name name of user preference
 * @property-read string $value value of user preference
 */
class UserPreference extends ValueObject
{
    /**
     * Name of user preference.
     *
     * Eg: timezone
     *
     * @var string
     */
    protected $name;

    /**
     * Value of user preference.
     *
     * Eg: America/New_York
     *
     * @var string
     */
    protected $value;
}
