<?php
namespace ezp\PublicAPI\Values\User;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class
 */
abstract class PolicyUpdateStruct extends ValueObject
{
    /**
     *
     * adds a limitation to the policy - if a Limitation exists with the same identifer
     * the existing limitation is replaced 
     * @param Limitation $limitation
     */
    public abstract function addLimitation(/*Limitation*/ $limitation);
    
}
?>


