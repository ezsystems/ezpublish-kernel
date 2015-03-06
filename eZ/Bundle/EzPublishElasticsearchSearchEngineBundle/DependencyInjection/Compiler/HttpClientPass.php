<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection\Compiler;

/**
 * HttpClientPass replaces $server argument of a Elasticsearch's HTTP Client
 * constructor, with a search engine's connection parameter resolved for current
 * siteaccess.
 *
 * @see \eZ\Publish\Core\Search\Elasticsearch\Content\Gateway\HttpClient\Stream
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
