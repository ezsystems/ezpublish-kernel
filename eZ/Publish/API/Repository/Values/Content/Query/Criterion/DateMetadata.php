<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\SPI\Repository\Values\Trash\Query\Criterion as TrashCriterion;
use InvalidArgumentException;

/**
 * A criterion that matches content based on one of the date metadata (created or modified).
 *
 * Supported Operators:
 * EQ, IN: matches content whose date is or belongs to a list of timestamps
 * GT, GTE: matches content whose date is greater than/greater than or equals the given timestamp
 * LT, LTE: matches content whose date is lower than/lower than or equals the given timestamp
 * BETWEEN: matches content whose date is between (included) the TWO given timestamps
 *
 * Example:
 * <code>
 * $createdCriterion = new Criterion\DateMetadata(
 *     Criterion\DateMetadata::CREATED,
 *     Operator::GTE,
 *     strtotime( 'yesterday' )
 * );
 * </code>
 */
class DateMetadata extends Criterion implements TrashCriterion
{
    public const MODIFIED = 'modified';

    public const CREATED = 'created';

    public const PUBLISHED = 'published';

    /**
     * (applies to TrashService::findTrashItems only).
     */
    public const TRASHED = 'trashed';

    public const TARGETS = [
        self::MODIFIED,
        self::CREATED,
        self::PUBLISHED,
        self::TRASHED,
    ];

    /**
     * Creates a new DateMetadata criterion on $metadata.
     *
     * @throws \InvalidArgumentException If target is unknown
     *
     * @param string $target One of DateMetadata::CREATED, DateMetadata::MODIFIED or DateMetadata::TRASHED (applies to TrashService::findTrashItems only)
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct(string $target, string $operator, $value)
    {
        if (!in_array($target, self::TARGETS)) {
            throw new InvalidArgumentException("Unknown DateMetadata $target");
        }
        parent::__construct($target, $operator, $value);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER),
            new Specifications(Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER, 2),
        ];
    }
}
