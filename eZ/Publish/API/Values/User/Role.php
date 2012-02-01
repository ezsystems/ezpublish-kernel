<?php

namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a role
 * 
 * @property-read int $id the internal id of the role
 * @property-read string $name the name of the role
 * @property-read string $description the description of the role
 * @property-read array $policies an array of the policies {@link \ezp\PublicAPI\Values\User\Policy} of the role.
 */
abstract class Role extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    protected $id;

    /**
     * Name of the role
     *
     * @var string
     */
    protected $name;

    /**
     * The description of the role
     * 
     * @since 5.0 
     *
     * @var string
     */
    protected $description;

    /**
     * returns the list of policies of this role
     * @return array an array of {@link \ezp\PublicAPI\Values\UserPolicy}
     */
    public abstract function getPolicies();

    /**
     * returns the policy for the given module and function
     * @return \ezp\PublicAPI\Values\UserPolicy
     */
    public abstract function getPolicy( $module, $function );
}
