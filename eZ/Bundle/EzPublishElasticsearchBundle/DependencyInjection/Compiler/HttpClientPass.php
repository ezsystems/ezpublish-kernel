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
class HttpClientPass extends ConnectionParameterPass
{
    protected function getServiceId()
    {
        return "ezpublish.search.elasticsearch.content.gateway.client.http.stream";
    }

    protected function getParameterName()
    {
        return "server";
    }

    protected function getReplacedArgumentIndex()
    {
        return 0;
    }
}
