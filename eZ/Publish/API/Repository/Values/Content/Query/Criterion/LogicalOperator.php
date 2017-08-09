<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use InvalidArgumentException;

/**
 * Note that the class should ideally have been in a Logical namespace, but it would have then be named 'And',
 * and 'And' is a PHP reserved word.
 */
abstract class LogicalOperator implements CriterionInterface
{
    /**
     * The set of criteria combined by the logical operator.
     *
     * @var CriterionInterface[]
     */
    public $criteria = array();

    /**
     * Creates a Logic operation with the given criteria.
     *
     * @param CriterionInterface[] $criteria
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $criteria)
    {
        foreach ($criteria as $key => $criterion) {
            if (!$criterion instanceof CriterionInterface) {
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

    public function getSpecifications()
    {
        throw new NotImplementedException('getSpecifications() not implemented for LogicalOperators');
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        throw new NotImplementedException('createFromQueryBuilder() not implemented for LogicalOperators');
    }
}
