<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\ContentTypeGroup
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 * ContentTypeGroup criterion.
 * Will match content whose ContentTypeId matches at least one of the given ContentTypeId
 *
 * Supported operators:
 * - IN
 * - EQ
 */
class ContentTypeGroup extends Criterion implements CriterionInterface
{
    /**
     * Creates a new ContentTypeGroup criterion
     *
     * Content will be matched if it matches one of the contentTypeGroupId in $value
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of contentTypeGroupId. $value must be an array of contentTypeGroupId
     *        - Operator::EQ: match against a single contentTypeGroupId. $value must be a single contentTypeGroupId
     * @param integer|array(integer) One or more contentTypeGroupId that must be matched
     *
     * @throw InvalidArgumentException if the parameters don't match what the criterion expects
     */
    public function __construct( $target = null, $operator, $value )
    {
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new OperatorSpecifications(
                Operator::IN,
                OperatorSpecifications::FORMAT_ARRAY
            ),
            new OperatorSpecifications(
                Operator::EQ,
                OperatorSpecifications::FORMAT_SINGLE
            )
        );
    }
}
?>
