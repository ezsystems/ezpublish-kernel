<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;

/**
 * This class represents a policy value
 *
 * @property-read array $limitations Limitations assigned to this policy
 */
class Policy extends APIPolicy
{
    /**
     * Limitations assigned to this policy
     *
     * @var array
     */
    protected $limitations = array();

    /**
     * Returns the list of limitations for this policy
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Limitation }
     */
    public function getLimitations()
    {
        return $this->limitations;
    }
}
