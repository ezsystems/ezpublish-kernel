<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\ContentValueViewTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class ContentValueViewTaggerSpec extends ObjectBehavior
{
    public function let(ResponseTagger $contentInfoTagger)
    {
        $this->beConstructedWith($contentInfoTagger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ContentValueViewTagger::class);
    }

    public function it_delegates_tagging_of_the_content_info(
        ResponseTagger $contentInfoTagger,
        ResponseCacheConfigurator $configurator,
        Response $response,
        ContentValueView $view
    ) {
        $contentInfo = new ContentInfo();
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);
        $view->getContent()->willReturn($content);

        $this->tag($configurator, $response, $view);

        $contentInfoTagger->tag($configurator, $response, $contentInfo)->shouldHaveBeenCalled();
    }
}
