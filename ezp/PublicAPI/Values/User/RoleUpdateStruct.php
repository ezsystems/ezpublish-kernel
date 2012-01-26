<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used to update a role
 *
 */
class RoleUpdateStruct extends ValueObject
{
    /**
     * Name of the role
     *
     * @var string
     */
    public $name;

    /**
     * 5.x The description of the role
     *
     * @var string
     */
    public $description;
}
