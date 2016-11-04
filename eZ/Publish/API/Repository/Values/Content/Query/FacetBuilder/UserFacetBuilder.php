<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a user facet.
 *
 * If provided the search service returns a UserFacet for the given type.
 */
class UserFacetBuilder extends FacetBuilder
{
    /**
     * Owner user.
     */
    const OWNER = 'owner';

    /**
     * Owner user group.
     */
    const GROUP = 'group';

    /**
     * Modifier.
     */
    const MODIFIER = 'modifier';

    /**
     * The type of the user facet.
     *
     * @var string
     */
    public $type = self::OWNER;
}
