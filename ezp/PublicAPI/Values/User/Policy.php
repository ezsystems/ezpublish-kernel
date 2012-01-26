<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;
use ezp\PubklicAPI\Values\User\Limitation;

/**
 * This class represents a policy value
 */
abstract class Policy extends ValueObject
{
    /**
     * ID of the policy
     *
     * @var mixed
     */
    public $id;

    /**
     * the ID of the role this policy belongs to
     *
     * @var mixed
     */
    public $roleId;

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
     * @return array an array of {@link Limitation}
     */
    public abstract function getLimitations();
}
