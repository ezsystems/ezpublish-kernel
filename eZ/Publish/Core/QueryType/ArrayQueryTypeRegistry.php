<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * A QueryType registry based on an array.
 */
class ArrayQueryTypeRegistry implements QueryTypeRegistry
{
    /** @var QueryType[] */
    private $registry = [];

    public function addQueryType($name, QueryType $queryType)
    {
        $this->registry[$name] = $queryType;
    }

    public function addQueryTypes(array $queryTypes)
    {
        $this->registry += $queryTypes;
    }

    public function getQueryType($name)
    {
        if (!isset($this->registry[$name])) {
            throw new InvalidArgumentException('QueryType name', 'No QueryType found with that name');
        }

        return $this->registry[$name];
    }
}
