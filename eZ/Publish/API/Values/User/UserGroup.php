<?php

namespace eZ\Publish\API\Values\User;

use eZ\Publish\API\Values\Content\Version;

use eZ\Publish\API\Values\ValueObject;

/**
 * This class represents a user group
 * 
 * @property-read int $id
 * @property-read int $parentId
 * @property-read int $subGroupCount
 */
abstract class UserGroup extends Content
{
    /**
     * The id of the user group
     *
     * @var integer
     */
    public $id;

    /**
     *
     * the parent id of the user group
     * @var integer
     */
    public $parentId;

    /**
     *
     * The number of sub groups
     * @var integer
     */
    public $subGroupCount;
}
