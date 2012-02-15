<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;
/**
 * This class represents a Limitation applied to a policy
 */
abstract class Limitation extends ValueObject
{
    // consts for BC
    const CONTENTTYPE = "Class";
    const LANGUAGE = "Language";
    const LOCATION = "Node";
    const OWNER = "Owner";
    const PARENTOWNER = "ParentOwner";
    const PARENTCONTENTTYPE = "ParentClass";
    const PARENTDEPTH = "ParentDepth";
    const SECTION = "Section";
    const SITEACCESS = "SiteAccess";
    const STATE = "State";
    const SUBTREE = "Subtree";
    const USERGROUP = "Group";
    const PARENTUSERGROUP = "ParentGroup";

    /**
     * Returns the limitation identifer (one of the defined constants) or a custom limitation
     *
     * @return string
     */
    abstract public function getIdentifier();

    /**
     * An integer list of ids or identifiers for which the limitation should be applied
     *
     * @var array of mixed
     */
    public $limitationValues;
}
