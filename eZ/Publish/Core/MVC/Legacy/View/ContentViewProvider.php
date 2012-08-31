<?php
/**
 * File containing the ContentViewProvider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy\View;

use eZ\Publish\MVC\View\ContentViewProvider as ContentViewProviderInterface,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\MVC\View\ContentView,
    eZ\Publish\Legacy\View\TwigContentViewLayoutDecorator,
    eZModule,
    Symfony\Component\HttpKernel\Log\LoggerInterface;

class ContentViewProvider implements ContentViewProviderInterface
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \eZ\Publish\Legacy\View\TwigContentViewLayoutDecorator
     */
    private $decorator;

    public function __construct( \Closure $legacyKernelClosure, TwigContentViewLayoutDecorator $decorator, LoggerInterface $logger = null )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->decorator = $decorator;
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }

    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     * @return \eZ\Publish\MVC\View\ContentView|void
     */
    public function getViewForContent( ContentInfo $contentInfo, $viewType )
    {
        // TODO: Implement getViewForContent() method.
    }

    /**
     * Returns a ContentView object corresponding to $location.
     * Will basically run content/view legacy module with appropriate parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @return \eZ\Publish\MVC\View\ContentView|void
     */
    public function getViewForLocation( Location $location, $viewType )
    {
        $legacyKernel = $this->getLegacyKernel();
        $logger = $this->logger;
        $legacyContentClosure = function ( array $params ) use ( $location, $viewType, $legacyKernel, $logger )
        {
            // Additional parameters (aka user parameters in legacy) are expected to be scalar
            foreach ( $params as $paramName => $param )
            {
                if ( !is_scalar( $param ) )
                {
                    unset( $params[$paramName] );
                    if ( isset( $logger ) )
                        $logger->notice(
                            "'$paramName' is not scalar, cannot pass it to legacy content module. Skipping.",
                            array( __METHOD__ )
                        );
                }
            }

            return $legacyKernel->runCallback(
                function () use ( $location, $viewType, $params )
                {
                    $contentViewModule = eZModule::findModule( 'content' );
                    $moduleResult = $contentViewModule->run(
                        'view',
                        array( $viewType, $location->id ),
                        false,
                        $params
                    );
                    // TODO: What about persistent variable & css/js added from ezjscore ? We ideally want to handle that as well
                    return $moduleResult['content'];
                },
                false
            );

        };

        $this->decorator->setContentView(
            new ContentView( $legacyContentClosure )
        );
        return $this->decorator;
    }
}
