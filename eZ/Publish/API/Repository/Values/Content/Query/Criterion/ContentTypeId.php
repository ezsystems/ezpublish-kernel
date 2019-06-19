<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches content based on its ContentType id.
 *
 * Supported operators:
 * - IN: will match from a list of ContentTypeId
 * - EQ: will match against one ContentTypeId
 */
class ContentTypeId extends Criterion
{
    /**
     * Creates a new ContentType criterion.
     *
     * Content will be matched if it matches one of the contentTypeId in $value
     *
     * @param int|int[] $value One or more content type Id that must be matched
     *
     * @throws \InvalidArgumentException if a non numeric id is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct($value)
    {
        parent::__construct(null, null, $value);
    }

    public function getSpecifications()
    {
        $types = Specifications::TYPE_INTEGER | Specifications::TYPE_STRING;

        return [
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY, $types),
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE, $types),
        ];
    }

    /**
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        return new self($value);
    }
}
