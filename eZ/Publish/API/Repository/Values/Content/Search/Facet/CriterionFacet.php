<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\CriterionFacet class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
