<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

/**
 * Registry of QueryType objects.
 */
class QueryTypeRegistry
{
    /** @var QueryType[] */
    private $registry = [];

    /**
     * Registers $queryType as $name.
     *
     * @param \eZ\Publish\Core\QueryType\QueryType $queryType
     */
    public function addQueryType(QueryType $queryType)
    {
        $this->registry[$queryType::getName()] = $queryType;
    }

    /**
     * Registers form the array $queryTypes.
     *
     * @param \eZ\Publish\Core\QueryType\QueryType[] $queryTypes An array of QueryTypes, with their name as the index
     */
    public function addQueryTypes(array $queryTypes)
    {
        $this->registry += $queryTypes;
    }

    /**
     * Get the QueryType $name.
     *
     * @param string $name
     *
     * @return \eZ\Publish\Core\QueryType\QueryType
     */
    public function getQueryType($name)
    {
        return $this->registry[$name];
    }
}
