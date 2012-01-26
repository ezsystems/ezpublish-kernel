<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\User\Limitation;

/**
 * This class is used to create a policy
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
     *
     * adds a limitation with the given identifier and list of values
     * @param Limitation $limitation
     */
    public abstract function addLimitation( /*Limitation*/ $limitation );
}
