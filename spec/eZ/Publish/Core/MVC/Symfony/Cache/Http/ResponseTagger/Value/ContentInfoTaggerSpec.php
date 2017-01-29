<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value\ContentInfoTagger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class ContentInfoTaggerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ContentInfoTagger::class);
    }

    public function it_ignores_non_content_info(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $this->tag($configurator, $response, null);

        $configurator->addTags()->shouldNotHaveBeenCalled();
    }

    public function it_tags_with_content_and_content_type_id(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new ContentInfo(['id' => 123, 'contentTypeId' => 987]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['content-123', 'content-type-987'])->shouldHaveBeenCalled();
    }

    public function it_tags_with_location_id_if_one_is_set(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new ContentInfo(['mainLocationId' => 456]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['location-456'])->shouldHaveBeenCalled();
    }
}
