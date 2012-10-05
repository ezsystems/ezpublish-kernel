<?php
/**
 * File containing the view Manager class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\MVC\Symfony\MVCEvents,
    eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent,
    Symfony\Component\Templating\EngineInterface,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Manager
{
    const VIEW_TYPE_FULL = 'full',
          VIEW_TYPE_LINE = 'line';

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array Array indexed by priority.
     *            Each priority key is an array of ContentViewProvider objects having this priority.
     *            The highest priority number is the highest priority
     */
    protected $viewProviders = array();

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider[]
     */
    protected $sortedViewProviders;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The base layout template to use when the view is requested to be generated
     * outside of the pagelayout.
     *
     * @var string
     */
    protected $viewBaseLayout;

    public function __construct( EngineInterface $templateEngine, EventDispatcherInterface $eventDispatcher, $viewBaseLayout, LoggerInterface $logger = null )
    {
        $this->templateEngine = $templateEngine;
        $this->eventDispatcher = $eventDispatcher;
        $this->viewBaseLayout = $viewBaseLayout;
        $this->logger = $logger;
    }

    /**
     * Registers $viewProvider as a valid view provider.
     * When this view provider will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider $viewProvider
     * @param int $priority
     */
    public function addViewProvider( ContentViewProvider $viewProvider, $priority = 0 )
    {
        $priority = (int)$priority;
        if ( !isset( $this->viewProviders[$priority] ) )
            $this->viewProviders[$priority] = array();

        $this->viewProviders[$priority][] = $viewProvider;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider[]
     */
    public function getAllViewProviders()
    {
        if ( empty( $this->sortedViewProviders ) )
            $this->sortedViewProviders = $this->sortViewProviders();

        return $this->sortedViewProviders;
    }

    /**
     * Sort the registered view providers by priority.
     * The highest priority number is the highest priority (reverse sorting)
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider[]
     */
    protected function sortViewProviders()
    {
        $sortedViewProviders = array();
        krsort( $this->viewProviders );

        foreach ( $this->viewProviders as $viewProviders )
        {
            $sortedViewProviders = array_merge( $sortedViewProviders, $viewProviders );
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
     * @throws \RuntimeException
     * @return string
     */
    public function renderContent( Content $content, $viewType = self::VIEW_TYPE_FULL, $parameters = array() )
    {
        $contentInfo = $content->getVersionInfo()->getContentInfo();
        foreach ( $this->getAllViewProviders() as $viewProvider )
        {
            $view = $viewProvider->getViewForContent( $contentInfo, $viewType );
            if ( $view instanceof ContentViewInterface )
            {
                $parameters['content'] = $content;
                return $this->renderContentView( $view, $parameters );
            }
        }

        throw new \RuntimeException( "Unable to find a template for #$contentInfo->id" );
    }

    /**
     * Renders $location by selecting the right template for $viewType.
     * $content and $location will be injected in the selected template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $viewType Variation of display for your content. Default is 'full'.
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty. 'location' and 'content'
     *        entries are reserved for the Location (and its Content) that is
     *        viewed.
     * @throws \RuntimeException
     * @return string
     */
    public function renderLocation( Location $location, Content $content, $viewType = self::VIEW_TYPE_FULL, $parameters = array() )
    {
        foreach ( $this->getAllViewProviders() as $viewProvider )
        {
            $view = $viewProvider->getViewForLocation( $location, $viewType );
            if ( $view instanceof ContentViewInterface )
            {
                $parameters['location'] = $location;
                $parameters['content'] = $content;
                return $this->renderContentView( $view, $parameters );
            }
        }

        throw new \RuntimeException( "Unable to find a view for location #$location->id" );
    }

    /**
     * Renders passed ContentView object via the template engine.
     * If $view's template identifier is a closure, then it is called directly and the result is returned as is.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     * @param array $defaultParams
     * @return string
     */
    public function renderContentView( ContentViewInterface $view, array $defaultParams = array() )
    {
        $defaultParams['viewbaseLayout'] = $this->viewBaseLayout;
        $view->addParameters( $defaultParams );
        $this->eventDispatcher->dispatch(
            MVCEvents::PRE_CONTENT_VIEW,
            new PreContentViewEvent( $view )
        );

        $templateIdentifier = $view->getTemplateIdentifier();
        $params = $view->getParameters();
        if ( $templateIdentifier instanceof \Closure )
            return $templateIdentifier( $params );

        return $this->templateEngine->render( $templateIdentifier, $params );
    }
}
