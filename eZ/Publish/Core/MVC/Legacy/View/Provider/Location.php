<?php
/**
 * File containing the View\Provider\Location class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

use eZ\Publish\Core\MVC\Legacy\View\Provider,
    eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProviderInterface,
    eZ\Publish\API\Repository\Values\Content\Location as APILocation,
    eZ\Publish\Core\MVC\Symfony\View\ContentView,
    eZModule;

class Location extends Provider implements LocationViewProviderInterface
{
    /**
     * Returns a ContentView object corresponding to $location.
     * Will basically run content/view legacy module with appropriate parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( APILocation $location, $viewType )
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
