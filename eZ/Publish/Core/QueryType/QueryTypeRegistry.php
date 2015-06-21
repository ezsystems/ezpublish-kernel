<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

interface QueryTypeRegistry
{
    /**
     * @return QueryType
     */
    public function getQueryType( $identifier );

    /**
     * @param QueryType[] $queryTypes
     */
    public function registerQueryTypes( array $queryTypes );
}
