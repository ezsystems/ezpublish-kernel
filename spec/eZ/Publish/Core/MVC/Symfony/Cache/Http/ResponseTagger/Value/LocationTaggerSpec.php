<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value\LocationTagger;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class LocationTaggerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(LocationTagger::class);
    }

    public function it_ignores_non_location(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $this->tag($configurator, $response, null);

        $configurator->addTags($response, Argument::any())->shouldNotHaveBeenCalled();
    }

    public function it_tags_with_location_id_if_not_main_location(
        ResponseCacheConfigurator $configurator,
        Response $response
    ) {
        $value = new Location(['id' => 123, 'contentInfo' => new ContentInfo(['mainLocationId' => 321])]);
        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['location-123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_parent_location_id(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new Location(['parentLocationId' => 123, 'contentInfo' => new ContentInfo()]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['parent-123'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_path_items(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new Location(['pathString' => '/1/2/123', 'contentInfo' => new ContentInfo()]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['path-1', 'path-2', 'path-123'])->shouldHaveBeenCalled();
    }
}
