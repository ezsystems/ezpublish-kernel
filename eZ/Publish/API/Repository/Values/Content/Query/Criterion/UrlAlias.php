<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\UrlAlias class.
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
 * A criterion that matches Content based on Url aliases.
 *
 * Supported operators:
 * - IN: will match from a list of URL aliases, wildcards allowed, using *:
 *   /articles/*
 * - EQ: strict match against one URL alias
 * - LIKE: fuzzy match using wildcards
 */
class UrlAlias extends Criterion implements CriterionInterface
{
    /**
     * Creates a new UrlAlias Criterion
     *
     * @param string $operator
     *        Possible values:
     *        - Operator::IN, requires an array of url alias as the $value
     *        - Operator::EQ, requires a single url alias as the $value
     *        - Operator::LIKE, requires a single url alias as the $value. Wilcards allowed
     * @param string|string[] $value an array of url alias
     *
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $operator, $value )
    {
        parent::__construct( null, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::LIKE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $operator, $value );
    }
}
