<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\eZ\Bundle\EzPublishCoreBundle\EventSubscriber;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class HttpCacheResponseSubscriberSpec extends ObjectBehavior
{
    public function let(
        FilterResponseEvent $event,
        Request $request,
        Response $response,
        ParameterBag $requestAttributes,
        ResponseCacheConfigurator $configurator,
        ResponseTagger $dispatcherTagger
    ) {
        $request->attributes = $requestAttributes;
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);

        $this->beConstructedWith($configurator, $dispatcherTagger);
    }

    public function it_does_not_enable_cache_if_the_view_is_not_a_cachableview(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        ParameterBag $requestAttributes,
        View $nonCachableView
    ) {
        $requestAttributes->get('view')->willReturn($nonCachableView);
        $configurator->enableCache()->shouldNotBecalled();

        $this->configureCache($event);
    }

    public function it_does_not_enable_cache_if_it_is_disabled_in_the_view(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes
    ) {
        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(false);
        $configurator->enableCache()->shouldNotBecalled();

        $this->configureCache($event);
    }

    public function it_enables_cache(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes,
        ResponseTagger $dispatcherTagger
    ) {
        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(true);

        $this->configureCache($event);

        $configurator->enableCache(Argument::type(Response::class))->shouldHaveBeenCalled();
        $configurator->setSharedMaxAge(Argument::type(Response::class))->shouldHaveBeenCalled();
        $dispatcherTagger->tag($configurator, Argument::type(Response::class), $view)->shouldHaveBeenCalled();
    }
}
