<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A criterion that just matches everything
 */
class MatchAll extends Criterion implements CriterionInterface
{
    /**
     * Creates a new MatchAll criterion
     */
    public function __construct()
    {
        // Do NOT call parent constructor. It tries to be too smart.
    }

    public function getSpecifications()
    {
        return array();
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self();
    }
}
