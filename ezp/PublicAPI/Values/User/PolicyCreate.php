<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used to create a policy
 */
abstract class PolicyCreate extends ValueObject
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
     * @param string identifier - the identifier of the limitation. Example identifiers
     *        Class, Section, ParentOwner, ParentClass, ParentGroup, ParentDepth, Language, Subtree, SiteAccess
     * @param array $values
     */
    public abstract function addLimitation($identifier,array $values);
}
?>

