<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\RelationList class.
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
 * A criterion that matches content based on the object relation field name
 *
 * Supported operators:
 * - IN: will match from a list of Content id found in a RelationList
 * - CONTAINS: will match against one Content id or a list of Content id found in a RelationList
 */
class RelationList extends Criterion implements CriterionInterface
{
    public function getSpecifications()
    {
        $types = Specifications::TYPE_INTEGER | Specifications::TYPE_STRING;
        return array(
            new Specifications( Operator::CONTAINS, Specifications::FORMAT_SINGLE | Specifications::FORMAT_ARRAY, $types ),
            new Specifications( Operator::IN, Specifications::FORMAT_ARRAY, $types ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $target, $operator, $value );
    }
}
