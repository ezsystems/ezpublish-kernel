<?php
namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class
 *
 * @property-read array $limitations List of limitations added to policy
 */
abstract class PolicyUpdateStruct extends ValueObject
{
    /**
     * List of limitations added to policy
     *
     * @var array
     */
    protected $limitations = array();

    /**
     *
     * adds a limitation to the policy - if a Limitation exists with the same identifer
     * the existing limitation is replaced
     * @param Limitation $limitation
     */
    abstract public function addLimitation( Limitation $limitation );

}
