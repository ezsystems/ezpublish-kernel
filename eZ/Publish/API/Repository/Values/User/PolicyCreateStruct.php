<?php
namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used to create a policy
 *
 * @property-read array $limitations List of limitations added to policy
 */
abstract class PolicyCreateStruct extends ValueObject
{
    /**
     * Name of module, associated with the Policy
     *
     * Eg: content
     *
     * @var string
     */
    public $module;

    /**
     * Name of the module function Or all functions with '*'
     *
     * Eg: read
     *
     * @var string
     */
    public $function;

    /**
     * List of limitations added to policy
     *
     * @var array
     */
    protected $limitations = array();

    /**
     *
     * adds a limitation with the given identifier and list of values
     * @param Limitation $limitation
     */
    abstract public function addLimitation( Limitation $limitation );
}
