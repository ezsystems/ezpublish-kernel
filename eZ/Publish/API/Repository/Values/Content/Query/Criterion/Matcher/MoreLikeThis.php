<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher\MoreLikeThis class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A more like this criterion is matched by content which contains similar terms
 * found in the given content, text or url fetch.
 */
class MoreLikeThis extends Matcher
{
    const CONTENT = 1;
    const TEXT = 2;
    const URL = 3;

    /**
     * The type of the parameter from which terms are extracted for finding similar objects.
     *
     * @var int
     */
    protected $type;

    /**
     * Creates a new more like this criterion.
     *
     * @param int $type the type (one of CONTENT,TEXT,URL)
     * @param mixed $value the value depending on the type
     *
     * @throws \InvalidArgumentException if the value type doesn't match the expected type
     */
    public function __construct($type, $value)
    {
        $this->type = $type;

        parent::__construct(null, null, $value);
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE),
        );
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self($value);
    }
}
