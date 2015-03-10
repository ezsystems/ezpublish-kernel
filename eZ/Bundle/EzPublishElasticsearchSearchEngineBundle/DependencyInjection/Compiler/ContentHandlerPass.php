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
 * ContentHandlerPass replaces $documentTypeName argument of a Elasticsearch's Content Handler
 * constructor, with a search engine's connection parameter resolved for current siteaccess.
 *
 * @see \eZ\Publish\Core\Search\Elasticsearch\Content\Handler
 */
class ContentHandlerPass extends ConnectionParameterPass
{
    protected function getServiceId()
    {
        return "ezpublish.spi.search.elasticsearch.content_handler";
    }

    protected function getParameterName()
    {
        return "document_type_name.content";
    }

    protected function getReplacedArgumentIndex()
    {
        return 3;
    }
}
