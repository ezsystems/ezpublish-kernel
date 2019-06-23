<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * A criterion that matches content based on its visibility.
 *
 * Warning: This Criterion acts on all locations of a Content, so it will include hidden
 * content within the tree you are searching for if content has visible location elsewhere.
 * This is intentional and you should rather use LocationSearch if this is not the behaviour you want.
 */
class Visibility extends Criterion
{
    /**
     * Visibility constant: visible.
     */
    const VISIBLE = 0;

    /**
     * Visibility constant: hidden.
     */
    const HIDDEN = 1;

    /**
     * Creates a new Visibility criterion.
     *
     * @param int $value Visibility: self::VISIBLE, self::HIDDEN
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($value)
    {
        if ($value !== self::VISIBLE && $value !== self::HIDDEN) {
            throw new InvalidArgumentException("Invalid visibility value $value");
        }

        parent::__construct(null, null, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
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
