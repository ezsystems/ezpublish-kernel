<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents a user group.
 *
 * @property-read mixed $parentId
 */
abstract class UserGroup extends Content
{
    /**
     * the parent id of the user group.
     *
     * @var mixed
     */
    protected $parentId;
}
