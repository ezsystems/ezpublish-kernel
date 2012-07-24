<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Repository;

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
     * Returns the limitation identifier (one of the defined constants) or a custom limitation
     *
     * @return string
     */
    abstract public function getIdentifier();

    /**
     * Evaluate permission against content and parent
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $placement In 'create' limitations; this is parent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If limitation data is inconsistent
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If wrong Value objects are used
     * @return bool
     */
    abstract public function evaluate( Repository $repository, ValueObject $object, ValueObject $placement = null );


    /**
     * Return Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If limitation data is inconsistent
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    //abstract public function getCriterion( Repository $repository );

    /**
     * An integer list of ids or identifiers for which the limitation should be applied
     *
     * @var array of mixed
     */
    public $limitationValues;
}
