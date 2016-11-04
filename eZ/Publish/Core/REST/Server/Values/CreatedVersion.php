<?php

/**
 * File containing the CreatedVersion class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created version.
 */
class CreatedVersion extends ValueObject
{
    /**
     * The created version.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\Version
     */
    public $version;
}
