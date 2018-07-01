<?php

/**
 * File containing the RoleAssignment class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

class RoleAssignment extends ValueObject
{
    /**
     * The role assignment id.
     *
     * @var mixed
     */
    public $id;

    /**
     * The Role connected to this assignment.
     *
     * @var mixed
     */
    public $roleId;

    /**
     * The user or user group id.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * One of 'Subtree' or 'Section'.
     *
     * @var string|null
     */
    public $limitationIdentifier;

    /**
     * The subtree paths or section ids.
     *
     * @var mixed[]|null
     */
    public $values;
}
