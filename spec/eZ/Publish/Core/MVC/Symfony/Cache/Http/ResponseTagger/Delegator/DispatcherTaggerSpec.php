<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\DispatcherTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class DispatcherTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $taggerOne, ResponseTagger $taggerTwo)
    {
        $this->beConstructedWith([$taggerOne, $taggerTwo]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DispatcherTagger::class);
    }

    public function it_calls_tag_on_every_tagger(
        ResponseTagger $taggerOne,
        ResponseTagger $taggerTwo,
        ResponseCacheConfigurator $configurator,
        Response $response,
        ValueObject $value
    ) {
        $this->tag($configurator, $response, $value);

        $taggerOne->tag($configurator, $response, $value)->shouldHaveBeenCalled();
        $taggerTwo->tag($configurator, $response, $value)->shouldHaveBeenCalled();
    }
}
