<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use Symfony\Component\HttpFoundation\Response;

class ContentInfoTagger implements ResponseTagger
{
    public function tag(ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        if (!$value instanceof ContentInfo) {
            return $this;
        }

        $configurator->addTags(
            $response,
            ['content-' . $value->id, 'content-type-' . $value->contentTypeId]
        );

        if ($value->mainLocationId) {
            $configurator->addTags($response, ['location-' . $value->mainLocationId]);
        }
    }
}
