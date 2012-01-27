<?php

namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\Content\Version;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a user group
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
