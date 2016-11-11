<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\DateRangeFacetBuilder class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a field range facet.
 *
 * If provided the search service returns a FieldRangeFacet for the given field path.
 * A field path starts with a field identifier and may contain a subpath in the case
 * of complex field types
 */
abstract class FieldRangeFacetBuilder extends FacetBuilder
{
    /**
     * The field path starts with a field identifier and a sub path (for complex types).
     *
     * @var string
     */
    public $fieldPath;

    /**
     * Adds a range entry with explicit to and unbounded from.
     *
     * @param mixed $to
     */
    abstract public function addUnboundedFrom($to);

    /**
     * Adds a range.
     *
     * @param mixed $from
     * @param mixed $to
     */
    abstract public function addRange($from, $to);

    /**
     * Adds a range entry with explicit from and unbounded to.
     *
     * @param mixed $from
     */
    abstract public function addUnboundedTo($from);
}
