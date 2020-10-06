<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a field term facet.
 *
 * If provided the search service returns a FieldFacet for the provided field path.
 * A field path starts with a field identifier and may contain a subpath in the case
 * of complex field types (e.g. author/name)
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
class FieldFacetBuilder extends FacetBuilder
{
    public const COUNT_ASC = 0;
    public const COUNT_DESC = 1;
    public const TERM_ASC = 2;
    public const TERM_DESC = 3;

    /**
     * The field paths starts with a field identifier and a sub path (for complex types).
     *
     * @var string[]
     */
    public $fieldPaths;

    /**
     * A regex filter for field values.
     *
     * @deprecated This field is not in use and will be replaced in the future by similar features.
     *
     * @var string
     */
    public $regex;

    /**
     * The sort order of the terms.
     *
     * One of FieldFacetBuilder::COUNT_ASC, FieldFacetBuilder::COUNT_DESC, FieldFacetBuilder::TERM_ASC, FieldFacetBuilder::TERM_DESC
     *
     * Note: A given Search engine might not support all options.
     *
     * @var int
     */
    public $sort;
}
