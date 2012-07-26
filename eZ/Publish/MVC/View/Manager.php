<?php
/**
 * File containing the view Manager class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\View;

use eZ\Publish\MVC\View\ContentViewProvider,
    eZ\Publish\MVC\View\ContentView,
    eZ\Publish\API\Repository\Values\Content\Content,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PreContentViewEvent,
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
     * @var \eZ\Publish\MVC\View\ContentViewProvider[]
     */
    protected $sortedViewProviders;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct( EngineInterface $templateEngine, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null )
    {
        $this->templateEngine = $templateEngine;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Registers $viewProvider as a valid view provider.
     * When this view provider will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\MVC\View\ContentViewProvider $viewProvider
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
     * @return \eZ\Publish\MVC\View\ContentViewProvider[]
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
     * @return \eZ\Publish\MVC\View\ContentViewProvider[]
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
     * @throws \RuntimeException
     * @return string
     */
    public function renderContent( Content $content, $viewType = self::VIEW_TYPE_FULL )
    {
        $contentInfo = $content->getVersionInfo()->getContentInfo();
        foreach ( $this->getAllViewProviders() as $viewProvider )
        {
            $view = $viewProvider->getViewForContent( $contentInfo, $viewType );
            if ( $view instanceof ContentView )
            {
                return $this->renderContentView( $view, array( 'content' => $content ) );
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
     * @throws \RuntimeException
     * @return string
     */
    public function renderLocation( Location $location, Content $content, $viewType = self::VIEW_TYPE_FULL )
    {
        foreach ( $this->getAllViewProviders() as $viewProvider )
        {
            $view = $viewProvider->getViewForLocation( $location, $viewType );
            if ( $view instanceof ContentView )
            {
                return $this->renderContentView(
                    $view,
                    array(
                         'location' => $location,
                         'content' => $content
                    )
                );
            }
        }

        throw new \RuntimeException( "Unable to find a view for location #$location->id" );
    }

    /**
     * Renders passed ContentView object via the template engine.
     * If $view's template identifier is a closure, then it is called directly and the result is returned as is.
     *
     * @param \eZ\Publish\MVC\View\ContentView $view
     * @param array $defaultParams
     * @return string
     */
    protected function renderContentView( ContentView $view, array $defaultParams = array() )
    {
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
