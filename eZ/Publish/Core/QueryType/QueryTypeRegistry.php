<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

/**
 * Registry of QueryType objects.
 */
interface QueryTypeRegistry
{
    /**
     * Registers $queryType.
     *
     * @param string $name
     * @param \eZ\Publish\Core\QueryType\QueryType $queryType
     */
    public function addQueryType(QueryType $queryType);

    /**
     * Registers QueryTypes from the $queryTypes array.
     *
     * @param \eZ\Publish\Core\QueryType\QueryType[] $queryTypes An array of QueryTypes.
     */
    public function addQueryTypes(array $queryTypes);

    /**
     * Get the QueryType named $name.
     *
     * @param string $name
     *
     * @return \eZ\Publish\Core\QueryType\QueryType
     */
    public function getQueryType($name);
}
