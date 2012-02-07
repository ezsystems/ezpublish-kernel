<?php
namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used to create a policy
 *
 * @property-read array $limitations List of limitations added to policy
 */
class PolicyCreateStruct extends APIPolicyCreateStruct
{
    /**
     * List of limitations added to policy
     * @todo move to abstract class
     *
     * @var array
     */
    protected $limitations = array();

    /**
     *
     * adds a limitation with the given identifier and list of values
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function addLimitation( Limitation $limitation )
    {
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }
}
