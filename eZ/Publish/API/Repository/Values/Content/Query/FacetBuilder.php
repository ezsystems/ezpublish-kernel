<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is the base class for facet builders.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
abstract class FacetBuilder extends ValueObject
{
    /**
     * The name of the facet.
     *
     * @var string
     */
    public $name;

    /**
     * If true the facet runs in a global mode not restricted by the query.
     *
     * @var bool
     */
    public $global = false;

    /**
     * An additional facet filter that will further filter the documents the facet will be executed on.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter = null;

    /**
     * Number of facets (terms) returned.
     *
     * @var int
     */
    public $limit = 10;

    /**
     * Specifies the minimum count. Only facet groups with more or equal results are returned.
     *
     * @var int
     */
    public $minCount = 1;
}
