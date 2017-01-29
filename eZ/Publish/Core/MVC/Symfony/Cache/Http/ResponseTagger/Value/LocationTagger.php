<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\Repository\Values\Content\Location;

class LocationTagger implements ResponseTagger
{
    public function tag(ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        if (!$value instanceof Location) {
            return $this;
        }

        if ($value->id !== $value->contentInfo->mainLocationId) {
            $configurator->addTags($response, ['location-' . $value->id]);
        }

        $configurator->addTags($response, ['parent-' . $value->parentLocationId]);
        $configurator->addTags(
            $response,
            array_map(
                function ($pathItem) {
                    return 'path-' . $pathItem;
                },
                $value->path
            )
        );

        return $this;
    }
}
