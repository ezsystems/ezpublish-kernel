<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use InvalidArgumentException;

/**
 * A criterion that matches content based on one of the user metadata (owner,
 * creator, modifier)
 *
 * Supported Operators:
 * EQ, IN: Matches the provided user ID(s) against the user IDs in the database
 *
 * Example:
 * <code>
 * $createdCriterion = new Criterion\UserMetadata(
 *     Criterion\UserMetadata::CREATOR,
 *     Operator::IN,
 *     array( 10, 14 )
 * );
 * </code>
 */
class UserMetadata extends Criterion implements CriterionInterface
{
    /**
     * UserMetadata target: Owner user
     */
    const OWNER = 'owner';

    /**
     * UserMetadata target: Owner user group
     */
    const GROUP = 'group';

    /**
     * UserMetadata target: Creator
     */
    const CREATOR = 'creator';

    /**
     * UserMetadata target: Modifier
     */
    const MODIFIER = 'modifier';

    /**
     * Creates a new UserMetadata criterion on $metadata
     *
     * @throws \InvalidArgumentException If target is unknown
     *
     * @param string $target One of UserMetadata::OWNER, UserMetadata::GROUP, UserMetadata::CREATED or UserMetadata::MODIFIED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct( $target, $operator, $value )
    {
        if ( $target != self::OWNER && $target != self::GROUP && $target != self::CREATOR && $target != self::MODIFIER )
        {
            throw new InvalidArgumentException( "Unknown UserMetadata $target" );
        }
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::IN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }
}
