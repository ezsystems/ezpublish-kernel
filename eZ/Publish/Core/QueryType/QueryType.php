<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * A Query container. It can return the Query it holds, with or without parameters.
 */
interface QueryType
{
    /**
     * Returns the executable query, with $parameters applied.
     *
     * @param array $parameters
     *
     * @return Query
     */
    public function getQuery( array $parameters = array() );
}
