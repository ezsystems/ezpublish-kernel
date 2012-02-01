<?php
namespace ezp\PublicAPI\Values\Content\Query\Criterion;
use ezp\PublicAPI\Values\Content\Query\Criterion,
    ezp\PublicAPI\Values\Content\Query\Criterion\Operator\Specifications,
    ezp\PublicAPI\Values\Content\Query\CriterionInterface,
    InvalidArgumentException;

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
     * @param string $target One of UserMetadata::OWNER, UserMetadata::GROUP, UserMetadata::CREATED or UserMetadata::MODIFIED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator*
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
