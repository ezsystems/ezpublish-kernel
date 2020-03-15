<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a role.
 *
 * @property-read int $id the internal id of the role
 * @property-read string $identifier the identifier of the role
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Policy[] $policies an array of the policies {@link \eZ\Publish\API\Repository\Values\User\Policy} of the role.
 */
abstract class Role extends ValueObject
{
    /** @var int Status constant for defined (aka "published") role */
    const STATUS_DEFINED = 0;

    /** @var int Status constant for draft (aka "temporary") role */
    const STATUS_DRAFT = 1;

    /**
     * ID of the user role.
     *
     * @var int
     */
    protected $id;

    /**
     * Readable string identifier of a role
     * in 4.x. this is mapped to the role name.
     *
     * @var string
     */
    protected $identifier;

    /**
     * The status of the role.
     *
     * @var int One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    protected $status;

    /**
     * Returns the list of policies of this role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    abstract public function getPolicies(): iterable;
}
