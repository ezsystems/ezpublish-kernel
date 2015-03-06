<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\DependencyInjection\Compiler;

/**
 *
 */
class ContentGatewayPass extends ConnectionParameterPass
{
    protected function getServiceId()
    {
        return "ezpublish.search.elasticsearch.content.gateway.native";
    }

    protected function getParameterName()
    {
        return "index_name";
    }

    protected function getReplacedArgumentIndex()
    {
        return 5;
    }
}
