<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use InvalidArgumentException;

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
}
