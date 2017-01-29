<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\LocationValueViewTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class LocationValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $locationTagger)
    {
        $this->beConstructedWith($locationTagger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocationValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_location(
        ResponseTagger $locationTagger,
        ResponseCacheConfigurator $configurator,
        Response $response,
        LocationValueView $view
    ) {
        $location = new Location();
        $view->getLocation()->willReturn($location);
        $this->tag($configurator, $response, $view);

        $locationTagger->tag($configurator, $response, $location)->shouldHaveBeenCalled();
    }
}
