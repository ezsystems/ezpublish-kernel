<?php

namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 *  This class represents a role
 *
 */
abstract class Role extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;

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
     * returns the list of policies of this role
     * @return array an array of {@link Policy}
     */
    public abstract function getPolicies();

    /**
     * returns the policy for the given module and function
     * @return Policy
     */
    public abstract function getPolicy( $module, $function );
}
