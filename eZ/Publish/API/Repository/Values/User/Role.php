<?php

namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a role
 * 
 * @property-read mixed $id the internal id of the role
 * @property-read string $name the name of the role
 * @property-read string $description the description of the role
 * @property-read array $policies an array of the policies {@link \eZ\Publish\API\Repository\Values\User\Policy} of the role.
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
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Policy}
     */
    abstract public function getPolicies();
}
