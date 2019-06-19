<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use InvalidArgumentException;

/**
 * Note that the class should ideally have been in a Logical namespace, but it would have then be named 'And',
 * and 'And' is a PHP reserved word.
 */
abstract class LogicalOperator extends Criterion
{
    /**
     * The set of criteria combined by the logical operator.
     *
     * @var Criterion[]
     */
    public $criteria = [];

    /**
     * Creates a Logic operation with the given criteria.
     *
     * @param Criterion[] $criteria
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $criteria)
    {
        foreach ($criteria as $key => $criterion) {
            if (!$criterion instanceof Criterion) {
                if ($criterion === null) {
                    $type = 'null';
                } elseif (is_object($criterion)) {
                    $type = get_class($criterion);
                } elseif (is_array($criterion)) {
                    $type = 'Array, with keys: ' . implode(', ', array_keys($criterion));
                } else {
                    $type = gettype($criterion) . ", with value: '{$criterion}'";
                }

                throw new InvalidArgumentException(
                    "Only Criterion objects are accepted, at index '{$key}': " . $type
                );
            }
            $this->criteria[] = $criterion;
        }
    }

    /**
     * @deprecated in LogicalOperators since 7.2.
     * It will be removed in 8.0 when Logical Operator no longer extends Criterion.
     */
    public function getSpecifications()
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        throw new NotImplementedException('getSpecifications() not implemented for LogicalOperators');
    }
}
