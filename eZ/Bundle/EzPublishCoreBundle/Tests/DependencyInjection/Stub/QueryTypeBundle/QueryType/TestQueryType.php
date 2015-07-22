<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;

class TestQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
    }

    public function getSupportedParameters()
    {
    }

    public static function getName()
    {
        return 'Test:Test';
    }
}
