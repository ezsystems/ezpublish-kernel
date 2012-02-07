<?php
namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;

/**
 * This class is used to create a new role
 *
 * @property-read array $policies Policies associated with the role
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
     * Policies associated with the role
     *
     * @var array
     */
    protected $policies = array();

    /**
     * Adds a policy to this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     */
    abstract public function addPolicy( PolicyCreateStruct $policyCreateStruct );

}
