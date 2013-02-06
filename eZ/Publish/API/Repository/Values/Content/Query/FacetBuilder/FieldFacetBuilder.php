<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder class.
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
 * Build a field term facet.
 *
 * If provided the search service returns a FieldFacet for the provided field path.
 * A field path starts with a field identifier and may contain a subpath in the case
 * of complex field types (e.g. author/name)
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */

class FieldFacetBuilder extends FacetBuilder
{
    const COUNT_ASC = 0;
    const COUNT_DESC = 1;
    const TERM_ASC = 2;
    const TERM_DESC = 3;

    /**
     * The field paths starts with a field identifier and a sub path (for complex types)
     *
     * @var string[]
     */
    public $fieldPaths;

    /**
     * A regex filter for field values
     *
     * @var string
     */
    public $regex;

    /**
     * The sort order of the terms
     *
     * One of FieldFacetBuilder::COUNT_ASC, FieldFacetBuilder::COUNT_DESC, FieldFacetBuilder::TERM_ASC, FieldFacetBuilder::TERM_DESC
     * @var int
     */
    public $sort;
}
