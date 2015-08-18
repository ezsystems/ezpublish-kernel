<?php

/**
 * File containing the Role class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class Role extends ValueObject
{
    /**
     * @var int Status constant for defined (aka "published") role
     */
    const STATUS_DEFINED = 0;

    /**
     * @var int Status constant for draft (aka "temporary") role
     */
    const STATUS_DRAFT = 1;

    /**
     * ID of the user rule.
     *
     * @var mixed
     */
    public $id;

    /**
     * Identifier of the role.
     *
     * Legacy note: Maps to name in 4.x.
     *
     * @var string
     */
    public $identifier;

    /**
     * Status of the role (legacy: "version").
     *
     * @since 6.0
     *
     * @var int One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    public $status;

    /**
     * Name of the role.
     *
     * @since 5.0
     *
     * @var string[]
     */
    public $name;

    /**
     * Name of the role.
     *
     * @since 5.0
     *
     * @var string[]
     */
    public $description = array();

    /**
     * Policies associated with the role.
     *
     * @var \eZ\Publish\SPI\Persistence\User\Policy[]
     */
    public $policies = array();
}
