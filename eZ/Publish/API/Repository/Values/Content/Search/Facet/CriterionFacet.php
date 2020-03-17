<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds the count of content matching the criterion.
 */
class CriterionFacet extends Facet
{
    /**
     * The count of objects matching the criterion.
     *
     * @var int
     */
    public $count;
}
