<?php
/**
 * File containing the ezp\Persistence\Content\Query\Criterion\ContentTypeGroup
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Query\Criterion;
use ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Content\Query\Criterion\Operator\Specifications,
    ezp\Persistence\Content\Query\CriterionInterface;

/**
 * A criterion that will match content based on its ContentTypeGroup id.
 * The ContentType must belong to at least one of the matched ContentTypeGroups
 *
 * Supported operators:
 * - IN: will match from a list of ContentTypeGroup id
 * - EQ: will match against one ContentTypeGroup id
 */
class ContentTypeGroupId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new ContentTypeGroup criterion
     *
     * Content will be matched if it matches one of the contentTypeGroupId in $value
     *
     * @param integer|array(integer) One or more contentTypeGroupId that must be matched
     *
     * @throw InvalidArgumentException if the parameters don't match what the criterion expects
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
                Specifications::FORMAT_ARRAY
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE
            )
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
?>
