<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * A QueryType registry that uses the QueryTypes names.
 */
class QueryTypeNameRegistry implements QueryTypeRegistry
{
    /** @var QueryType[] */
    private $registry = [];

    public function addQueryType(QueryType $queryType)
    {
        $this->registry[$queryType::getName()] = $queryType;
    }

    public function addQueryTypes(array $queryTypes)
    {
        foreach ($queryTypes as $queryType) {
            $this->addQueryType($queryType);
        }
    }

    public function getQueryType($name)
    {
        if (!isset($this->registry[$name])) {
            throw new InvalidArgumentException($name, 'No QueryType found with that name');
        }

        return $this->registry[$name];
    }
}
