<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for facets.
 */
abstract class Facet extends ValueObject
{
    /**
     * The name of the facet.
     *
     * @var string
     */
    public $name;
}
