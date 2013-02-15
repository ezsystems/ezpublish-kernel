<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU General Public License v2.0
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A criterion that matches content based on its ContentType Identifier
 *
 * Supported operators:
 * - IN: will match from a list of ContentTypeIdentifier
 * - EQ: will match against one ContentTypeIdentifier
 */
class ContentTypeIdentifier extends Criterion implements CriterionInterface
{
    /**
     * Creates a new ContentType criterion
     *
     * Content will be matched if it matches one of the contentTypeIdentifier in $value
     *
     * @param string|string[] $value One or more content type identifiers that must be matched
     *
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value )
    {
        parent::__construct( null, null, $value );
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
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}

