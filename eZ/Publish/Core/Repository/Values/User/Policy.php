<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class represents a policy value
 *
 * @property-read int $id internal id of the policy
 * @property-read int $roleId the role id this policy belongs to
 * @property-read string $module Name of module, associated with the Policy
 * @property-read string $function  Name of the module function Or all functions with '*'
 * @property-read array $limitations an array of \eZ\Publish\API\Repository\Values\User\Limitation
 */
class Policy extends APIPolicy
{
    /**
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Limitation }
     */
    public function getLimitations()
    {
        // @todo implement
    }
}
