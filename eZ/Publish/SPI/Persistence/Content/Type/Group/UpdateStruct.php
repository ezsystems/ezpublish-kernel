<?php

/**
 * File containing the UpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Type\Group;

use eZ\Publish\SPI\Persistence\ValueObject;

class UpdateStruct extends ValueObject
{
    /**
     * Primary key.
     *
     * @var mixed
     */
    public $id;

    /**
     * Name.
     *
     * @since 5.0
     *
     * @var string[]
     */
    public $name = [];

    /**
     * Description.
     *
     * @since 5.0
     *
     * @var string[]
     */
    public $description = [];

    /**
     * Readable string identifier of a group.
     *
     * @var string
     */
    public $identifier;

    /**
     * Modified date (timestamp).
     *
     * @var int
     */
    public $modified;

    /**
     * Modifier user id.
     *
     * @var mixed
     */
    public $modifierId;
}
