<?php
/**
 * File containing the ContentViewProvider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider as ContentViewProviderInterface,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\MVC\Symfony\View\ContentView,
    eZ\Publish\Core\MVC\Legacy\View\TwigContentViewLayoutDecorator,
    eZModule,
    eZTemplate,
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
     * @var \eZ\Publish\Core\MVC\Legacy\View\TwigContentViewLayoutDecorator
     */
    private $decorator;

    public function __construct( \Closure $legacyKernelClosure, TwigContentViewLayoutDecorator $decorator, LoggerInterface $logger = null )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->decorator = $decorator;
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
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
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getViewForContent( ContentInfo $contentInfo, $viewType )
    {
        $legacyKernel = $this->getLegacyKernel();
        $legacyContentClosure = function ( array $params ) use ( $contentInfo, $viewType, $legacyKernel )
        {
            return $legacyKernel->runCallback(
                function () use ( $contentInfo, $viewType, $params )
                {
                    $tpl = eZTemplate::factory();
                    /**
                     * @var \eZObjectForwarder
                     */
                    $funcObject = $tpl->fetchFunctionObject( 'content_view_gui' );
                    $children = array();
                    $params['content_object'] = array(
                        array(
                            eZTemplate::TYPE_ARRAY,
                            // eZTemplate::TYPE_OBJECT does not exist because
                            // it's not possible to create "inline" objects in
                            // legacy template engine (ie objects are always
                            // stored in a tpl variable).
                            // TYPE_ARRAY is used here to allow to directly
                            // retrieve the object without creating a variable.
                            // (TYPE_STRING, TYPE_BOOLEAN, ... have the same
                            // behaviour, see eZTemplate::elementValue())
                            \eZContentObject::fetch( $contentInfo->id )
                        )
                    );
                    $params['view'] = array(
                        array(
                            eZTemplate::TYPE_STRING,
                            $viewType
                        )
                    );
                    $funcObject->process(
                        $tpl, $children, 'content_view_gui', false,
                        $params, array(), '', ''
                    );
                    if ( is_array( $children ) && isset( $children[0] ) )
                    {
                        return $children[0];
                    }
                    return '';
                },
                false
            );
        };
        $this->decorator->setContentView(
            new ContentView( $legacyContentClosure )
        );
        return $this->decorator;
    }

    /**
     * Returns a ContentView object corresponding to $location.
     * Will basically run content/view legacy module with appropriate parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
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
