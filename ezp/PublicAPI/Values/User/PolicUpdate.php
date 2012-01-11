<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class
 */
abstract class PolicyUpdate extends ValueObject
{
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


