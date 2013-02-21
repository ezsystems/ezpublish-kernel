<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a user facet
 *
 * If provided the search service returns a UserFacet for the given type.
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
class UserFacetBuilder extends FacetBuilder
{
    /**
     * Owner user
     */
    const OWNER = 'owner';

    /**
     * Owner user group
     */
    const GROUP = 'group';

    /**
     * Creator
     */
    const CREATOR = 'creator';

    /**
     * Modifier
     */
    const MODIFIER = 'modifier';

    /**
     * The type of the user facet
     *
     * @var string
     */
    public $type = UserFacetBuilder::OWNER;
}
