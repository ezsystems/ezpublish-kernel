<?php

/**
 * File containing the view Manager class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Templating\EngineInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class Manager implements ViewManagerInterface
{
    /** @var \Symfony\Component\Templating\EngineInterface */
    protected $templateEngine;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @var array Array indexed by priority.
     *            Each priority key is an array of Content View Provider objects having this priority.
     *            The highest priority number is the highest priority
     */
    protected $contentViewProviders = [];

    /**
     * @var array Array indexed by priority.
     *            Each priority key is an array of Location View Provider objects having this priority.
     *            The highest priority number is the highest priority
     */
    protected $locationViewProviders = [];

    /**
     * @var array Array indexed by priority.
     *            Each priority key is an array of Block View Provider objects having this priority.
     *            The highest priority number is the highest priority
     */
    protected $blockViewProviders = [];

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Provider\Content[] */
    protected $sortedContentViewProviders;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Provider\Location[] */
    protected $sortedLocationViewProviders;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Provider\Block[] */
    protected $sortedBlockViewProviders;

    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * The base layout template to use when the view is requested to be generated
     * outside of the pagelayout.
     *
     * @var string
     */
    protected $viewBaseLayout;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator */
    private $viewConfigurator;

    public function __construct(
        EngineInterface $templateEngine,
        EventDispatcherInterface $eventDispatcher,
        Repository $repository,
        ConfigResolverInterface $configResolver,
        $viewBaseLayout,
        $viewConfigurator,
        LoggerInterface $logger = null
    ) {
        $this->templateEngine = $templateEngine;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->viewBaseLayout = $viewBaseLayout;
        $this->logger = $logger;
        $this->viewConfigurator = $viewConfigurator;
    }

    /**
     * Helper for {@see addContentViewProvider()} and {@see addLocationViewProvider()}.
     *
     * @param array $property
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProvider $viewProvider
     * @param int $priority
     */
    private function addViewProvider(&$property, $viewProvider, $priority)
    {
        $priority = (int)$priority;
        if (!isset($property[$priority])) {
            $property[$priority] = [];
        }

        $property[$priority][] = $viewProvider;
    }

    /**
     * Registers $viewProvider as a valid content view provider.
     * When this view provider will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProvider $viewProvider
     * @param int $priority
     */
    public function addContentViewProvider(ViewProvider $viewProvider, $priority = 0)
    {
        $this->addViewProvider($this->contentViewProviders, $viewProvider, $priority);
    }

    /**
     * Registers $viewProvider as a valid location view provider.
     * When this view provider will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProvider $viewProvider
     * @param int $priority
     */
    public function addLocationViewProvider(ViewProvider $viewProvider, $priority = 0)
    {
        $this->addViewProvider($this->locationViewProviders, $viewProvider, $priority);
    }

    /**
     * Registers $viewProvider as a valid location view provider.
     * When this view provider will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Provider\Block $viewProvider
     * @param int $priority
     */
    public function addBlockViewProvider(ViewProvider $viewProvider, $priority = 0)
    {
        $this->addViewProvider($this->blockViewProviders, $viewProvider, $priority);
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[]
     */
    public function getAllContentViewProviders()
    {
        if (empty($this->sortedContentViewProviders)) {
            $this->sortedContentViewProviders = $this->sortViewProviders($this->contentViewProviders);
        }

        return $this->sortedContentViewProviders;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[]
     */
    public function getAllLocationViewProviders()
    {
        if (empty($this->sortedLocationViewProviders)) {
            $this->sortedLocationViewProviders = $this->sortViewProviders($this->locationViewProviders);
        }

        return $this->sortedLocationViewProviders;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[]
     */
    public function getAllBlockViewProviders()
    {
        if (empty($this->sortedBlockViewProviders)) {
            $this->sortedBlockViewProviders = $this->sortViewProviders($this->blockViewProviders);
        }

        return $this->sortedBlockViewProviders;
    }

    /**
     * Sort the registered view providers by priority.
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @param array $property view providers to sort
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\Provider\Content[]|\eZ\Publish\Core\MVC\Symfony\View\Provider\Location[]|\eZ\Publish\Core\MVC\Symfony\View\Provider\Block[]
     */
    protected function sortViewProviders($property)
    {
        $sortedViewProviders = [];
        krsort($property);

        foreach ($property as $viewProvider) {
            $sortedViewProviders = array_merge($sortedViewProviders, $viewProvider);
        }

        return $sortedViewProviders;
    }

    /**
     * Renders $content by selecting the right template.
     * $content will be injected in the selected template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $viewType Variation of display for your content. Default is 'full'.
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty. 'content' entry is
     *        reserved for the Content that is rendered.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderContent(Content $content, $viewType = ViewManagerInterface::VIEW_TYPE_FULL, $parameters = [])
    {
        $view = new ContentView(null, $parameters, $viewType);
        $view->setContent($content);
        if (isset($parameters['location'])) {
            $view->setLocation($parameters['location']);
        }

        $this->viewConfigurator->configure($view);

        if ($view->getTemplateIdentifier() === null) {
            throw new RuntimeException('Unable to find a template for #' . $content->contentInfo->id);
        }

        return $this->renderContentView($view, $parameters);
    }

    /**
     * Renders $location by selecting the right template for $viewType.
     * $content and $location will be injected in the selected template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content. Default is 'full'.
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty. 'location' and 'content'
     *        entries are reserved for the Location (and its Content) that is
     *        viewed.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderLocation(Location $location, $viewType = ViewManagerInterface::VIEW_TYPE_FULL, $parameters = [])
    {
        if (!isset($parameters['location'])) {
            $parameters['location'] = $location;
        }

        if (!isset($parameters['content'])) {
            $parameters['content'] = $this->repository->getContentService()->loadContentByContentInfo(
                $location->contentInfo,
                $this->configResolver->getParameter('languages')
            );
        }

        return $this->renderContent($parameters['content'], $viewType, $parameters);
    }

    /**
     * Renders $block by selecting the right template.
     * $block will be injected in the selected template.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty.
     *        'block' entry is reserved for the Block that is viewed.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderBlock(Block $block, $parameters = [])
    {
        $view = new BlockView(null, $parameters);
        $view->setBlock($block);

        $this->viewConfigurator->configure($view);

        if ($view->getTemplateIdentifier() === null) {
            throw new RuntimeException("Unable to find a view for location #$block->id");
        }

        return $this->renderContentView($view);
    }

    /**
     * Renders passed ContentView object via the template engine.
     * If $view's template identifier is a closure, then it is called directly and the result is returned as is.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     * @param array $defaultParams
     *
     * @return string
     */
    public function renderContentView(View $view, array $defaultParams = [])
    {
        $defaultParams['viewbaseLayout'] = $this->viewBaseLayout;
        $view->addParameters($defaultParams);
        $this->eventDispatcher->dispatch(
            MVCEvents::PRE_CONTENT_VIEW,
            new PreContentViewEvent($view)
        );

        $templateIdentifier = $view->getTemplateIdentifier();
        $params = $view->getParameters();
        if ($templateIdentifier instanceof \Closure) {
            return $templateIdentifier($params);
        }

        return $this->templateEngine->render($templateIdentifier, $params);
    }
}
