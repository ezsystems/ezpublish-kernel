<?php
namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;

/**
 * This class is used to create a new role
 */
abstract class RoleCreateStruct extends ValueObject
{

    /**
     * Name of the role
     *
     * @var string
     */
    public $name;

    /**
     * The description of the role
     *
     * @since 5.x
     * @var string
     */
    public $description;

    /**
     * Returns policies associated with the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    public abstract function getPolicies();

    /**
     * Adds a policy to this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     */
    abstract public function addPolicy( PolicyCreateStruct $policyCreateStruct );

}
