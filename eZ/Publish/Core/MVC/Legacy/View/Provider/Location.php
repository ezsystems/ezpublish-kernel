<?php
/**
 * File containing the View\Provider\Location class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

use eZ\Publish\Core\MVC\Legacy\View\Provider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProviderInterface;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZModule;
use ezjscPacker;
use eZINI;
use Symfony\Component\HttpFoundation\Request;

class Location extends Provider implements LocationViewProviderInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function setRequest( Request $request )
    {
        $this->request = $request;
    }

    /**
     * Returns a ContentView object corresponding to $location.
     * Will basically run content/view legacy module with appropriate parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( APILocation $location, $viewType )
    {
        $legacyKernel = $this->getLegacyKernel();
        $logger = $this->logger;
        $legacyHelper = $this->legacyHelper;
        $viewParameters = array();
        if ( isset( $this->request ) )
            $viewParameters = $this->request->attributes->get( 'viewParameters', array() );

        $legacyContentClosure = function ( array $params ) use ( $location, $viewType, $legacyKernel, $logger, $legacyHelper, $viewParameters )
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

            // viewbaseLayout is useless in legacy views
            unset( $params['viewbaseLayout'] );
            $params += $viewParameters;

            return $legacyKernel->runCallback(
                function () use ( $location, $viewType, $params, $legacyHelper )
                {
                    $contentViewModule = eZModule::findModule( 'content' );
                    $moduleResult = $contentViewModule->run(
                        'view',
                        array( $viewType, $location->id ),
                        false,
                        $params
                    );

                    // Injecting all $moduleResult entries in the legacy helper
                    foreach ( $moduleResult as $key => $val )
                    {
                        if ( $key === 'content' )
                            continue;

                        $legacyHelper->set( $key, $val );
                    }

                    // Javascript/CSS files required with ezcss_require/ezscript_require
                    // Compression level is forced to 0 to only get the files list
                    if ( isset( $moduleResult['content_info']['persistent_variable']['css_files'] ) )
                    {
                        $legacyHelper->set(
                            'css_files',
                            ezjscPacker::buildStylesheetFiles(
                                $moduleResult['content_info']['persistent_variable']['css_files'],
                                0
                            )
                        );
                    }
                    if ( isset( $moduleResult['content_info']['persistent_variable']['js_files'] ) )
                    {
                        $legacyHelper->set(
                            'js_files',
                            ezjscPacker::buildJavascriptFiles(
                                $moduleResult['content_info']['persistent_variable']['js_files'],
                                0
                            )
                        );
                    }

                    // Now getting configured JS/CSS files, in design.ini
                    // Will only take FrontendCSSFileList/FrontendJavascriptList
                    $designINI = eZINI::instance( 'design.ini' );
                    $legacyHelper->set(
                        'css_files_configured',
                        ezjscPacker::buildStylesheetFiles(
                            $designINI->variable( 'StylesheetSettings', 'FrontendCSSFileList' ),
                            0
                        )
                    );
                    $legacyHelper->set(
                        'js_files_configured',
                        ezjscPacker::buildJavascriptFiles(
                            $designINI->variable( 'JavaScriptSettings', 'FrontendJavaScriptList' ),
                            0
                        )
                    );

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

    /**
     * Checks if $valueObject matches the $matcher's rules.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher $matcher
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @throws \InvalidArgumentException If $valueObject is not of expected sub-type.
     *
     * @return bool
     */
    public function match( ViewProviderMatcher $matcher, ValueObject $valueObject )
    {
        return true;
    }
}
