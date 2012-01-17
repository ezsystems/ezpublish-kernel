<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used to create a new role
 */
abstract class RoleCreate extends ValueObject
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

    /**
     *
     * adds a policy to this role
     * @param PolicyCreate $policyCreate
     */
    public abstract function addPolicy(/*PolicyCreate*/ $policyCreate);

}
?>

