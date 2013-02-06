<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\CriterionFacet class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds the count of content matching the criterion
 */
class CriterionFacet extends Facet
{
    /**
     * The count of objects matching the criterion
     *
     * @var int
     */
    public $count;
}
