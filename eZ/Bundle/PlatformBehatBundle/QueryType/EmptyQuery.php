<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;

abstract class EmptyQuery
{
    public function getQuery(array $parameters = [])
    {
        return new Query();
    }

    public function getSupportedParameters()
    {
        return [];
    }
}
