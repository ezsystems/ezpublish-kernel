<?php

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents a user group
 * 
 * @property-read mixed $id
 * @property-read mixed $parentId
 * @property-read int $subGroupCount
 */
abstract class UserGroup extends Content
{
    /**
     * The id of the user group
     *
     * @var mixed
     */
    protected $id;

    /**
     *
     * the parent id of the user group
     * @var mixed
     */
    protected $parentId;

    /**
     *
     * The number of sub groups
     * @var integer
     */
    protected $subGroupCount;
}
