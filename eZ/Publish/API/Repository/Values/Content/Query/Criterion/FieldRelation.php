<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\FieldRelation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches Content based on the relations in relation field.
 * This includes Relation and RelationList field types in standard installation, but also any
 * other field type storing {@link \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
 * type relation.
 *
 * Supported operators:
 * - IN: will match if Content relates to one or more of the given ids through given relation field
 * - CONTAINS: will match if Content relates to all of the given ids through given relation field
 */
class FieldRelation extends Criterion
{
    public function getSpecifications()
    {
        $types = Specifications::TYPE_INTEGER | Specifications::TYPE_STRING;

        return [
            new Specifications(Operator::CONTAINS, Specifications::FORMAT_SINGLE | Specifications::FORMAT_ARRAY, $types),
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY, $types),
        ];
    }

    /**
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        return new self($target, $operator, $value);
    }
}
