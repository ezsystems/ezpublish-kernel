<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a date range facet.
 *
 * If provided the search service returns a DateRangeFacet depending on the provided
 * type (PUBLISHED, CREATED, MODIFIED)
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
abstract class DateRangeFacetBuilder extends FacetBuilder
{
    public const PUBLISHED = 0;
    public const CREATED = 1;
    public const MODIFIED = 2;

    public $type = self::PUBLISHED;

    /**
     * Adds a range entry with explicit to and unbounded from.
     *
     * @param \DateTime $to
     */
    abstract public function addUnboundedFrom($to);

    /**
     * Adds a date range.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     */
    abstract public function addRange($from, $to);

    /**
     * Adds a range entry with explicit from and unbounded to.
     *
     * @param \DateTime $from
     */
    abstract public function addUnboundedTo($from);
}
