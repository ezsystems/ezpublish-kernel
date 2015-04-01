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
 * LocationGatewayPass replaces $indexName argument of a Elasticsearch's Location Gateway
 * constructor, with a search engine's connection parameter resolved for current siteaccess.
 *
 * @see \eZ\Publish\Core\Search\Elasticsearch\Content\Gateway\Native
 */
class LocationGatewayPass extends ConnectionParameterPass
{
    protected function getServiceId()
    {
        return "ezpublish.search.elasticsearch.location.gateway.native";
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
