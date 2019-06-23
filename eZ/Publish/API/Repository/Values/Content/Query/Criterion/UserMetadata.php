<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * A criterion that matches content based on one of the user metadata (owner,
 * group, modifier).
 *
 * Supported Operators:
 * EQ, IN: Matches the provided user ID(s) against the user IDs in the database
 *
 * Example:
 * <code>
 * $createdCriterion = new Criterion\UserMetadata(
 *     Criterion\UserMetadata::OWNER,
 *     Operator::IN,
 *     array( 10, 14 )
 * );
 * </code>
 */
class UserMetadata extends Criterion
{
    /**
     * UserMetadata target: Owner user.
     */
    const OWNER = 'owner';

    /**
     * UserMetadata target: Owner user group.
     */
    const GROUP = 'group';

    /**
     * UserMetadata target: Modifier.
     */
    const MODIFIER = 'modifier';

    /**
     * Creates a new UserMetadata criterion on $metadata.
     *
     * @throws \InvalidArgumentException If target is unknown
     *
     * @param string $target One of UserMetadata::OWNER, UserMetadata::GROUP or UserMetadata::MODIFIED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct($target, $operator, $value)
    {
        switch ($target) {
            case self::OWNER:
            case self::GROUP:
            case self::MODIFIER:
                parent::__construct($target, $operator, $value);

                return;
        }

        throw new InvalidArgumentException("Unknown UserMetadata $target");
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        ];
    }
}
