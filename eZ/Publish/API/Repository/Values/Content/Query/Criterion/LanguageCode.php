<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * A criterion that matches content based on its language code and always-available state.
 *
 * Supported operators:
 * - IN: matches against a list of language codes
 * - EQ: matches against one language code
 */
class LanguageCode extends Criterion
{
    /**
     * Switch for matching Content that is always-available.
     *
     * @var bool
     */
    public $matchAlwaysAvailable;

    /**
     * Creates a new LanguageCode criterion.
     *
     * @param string|string[] $value One or more language codes that must be matched
     * @param bool $matchAlwaysAvailable Denotes if always-available Content is to be matched regardless
     *                                      of language codes, this is the default behaviour
     *
     * @throws \InvalidArgumentException if non string value is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct($value, $matchAlwaysAvailable = true)
    {
        if (!is_bool($matchAlwaysAvailable)) {
            throw new InvalidArgumentType('matchAlwaysAvailable', 'boolean', $matchAlwaysAvailable);
        }

        $this->matchAlwaysAvailable = $matchAlwaysAvailable;
        parent::__construct(null, null, $value);
    }

    public function getSpecifications()
    {
        return [
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
