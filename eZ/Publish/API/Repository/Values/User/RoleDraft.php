<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class represents a draft of a role.
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\PolicyDraft[] $policies
 */
abstract class RoleDraft extends Role
{
}
