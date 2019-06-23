<?php

/**
 * File containing the Content Type Group CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Type\Group;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
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
     * Created date (timestamp).
     *
     * @var int
     */
    public $created;

    /**
     * Modified date (timestamp).
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id.
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id.
     *
     * @var mixed
     */
    public $modifierId;
}
