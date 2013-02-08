<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

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
    const NEWSECTION = "NewSection";
    const SITEACCESS = "SiteAccess";
    const STATE = "State";
    const SUBTREE = "Subtree";
    const USERGROUP = "Group";
    const PARENTUSERGROUP = "ParentGroup";

    /**
     * Returns the limitation identifier (one of the defined constants) or a custom limitation
     *
     * @return string
     */
    abstract public function getIdentifier();

    /**
     * An list of IDs or identifiers for which the limitation should be applied
     *
     * The value of this property must conform to a hash, which means that it
     * may only consist of array and scalar values, but must not contain objects
     * or resources.
     *
     * @readonly
     * @var mixed[]
     */
    public $limitationValues;
}
