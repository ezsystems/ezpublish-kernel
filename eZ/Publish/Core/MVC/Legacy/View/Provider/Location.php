<?php
/**
 * File containing the View\Provider\Location class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View\Provider;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper;
use eZ\Publish\Core\MVC\Legacy\View\Provider;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProviderInterface;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZModule;
use ezpEvent;
use Symfony\Component\HttpFoundation\Request;

class Location extends Provider implements LocationViewProviderInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function setRequest( Request $request = null )
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
        $logger = $this->logger;
        $legacyHelper = $this->legacyHelper;
        $currentViewProvider = $this;
        $viewParameters = array();
        if ( isset( $this->request ) )
            $viewParameters = $this->request->attributes->get( 'viewParameters', array() );

        $legacyContentClosure = function ( array $params ) use ( $location, $viewType, $logger, $legacyHelper, $viewParameters, $currentViewProvider )
        {
            $content = isset( $params['content'] ) ? $params['content'] : null;
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

            // Render preview or published view depending on context.
            if ( isset( $params['isPreview'] ) && $params['isPreview'] === true && $content instanceof APIContent )
            {
                return $currentViewProvider->renderPreview( $content, $params, $legacyHelper );
            }
            else
            {
                return $currentViewProvider->renderPublishedView( $location, $viewType, $params, $legacyHelper );
            }
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

    /**
     * Returns published view for $location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @param array $params Hash of arbitrary parameters to pass to final view
     * @param \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper $legacyHelper
     *
     * @return string
     */
    public function renderPublishedView( APILocation $location, $viewType, array $params, LegacyHelper $legacyHelper )
    {
        $moduleResult = array();

        // Filling up moduleResult
        $result = $this->getLegacyKernel()->runCallback(
            function () use ( $location, $viewType, $params, &$moduleResult )
            {
                $contentViewModule = eZModule::findModule( 'content' );
                $moduleResult = $contentViewModule->run(
                    'view',
                    array( $viewType, $location->id ),
                    false,
                    $params
                );

                return ezpEvent::getInstance()->filter( 'response/output', $moduleResult['content'] );
            },
            false
        );

        $legacyHelper->loadDataFromModuleResult( $moduleResult );

        return $result;
    }

    /**
     * Returns preview for $content (versionNo to display is held in $content->versionInfo).
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $params Hash of arbitrary parameters to pass to final view
     * @param \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper $legacyHelper
     *
     * @return string
     */
    public function renderPreview( APIContent $content, array $params, LegacyHelper $legacyHelper )
    {
        /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess */
        $siteAccess = $this->request->attributes->get( 'siteaccess' );
        $moduleResult = array();

        // Filling up moduleResult
        $result = $this->getLegacyKernel()->runCallback(
            function () use ( $content, $params, $siteAccess, &$moduleResult )
            {
                $contentViewModule = eZModule::findModule( 'content' );
                $moduleResult = $contentViewModule->run(
                    'versionview',
                    array( $content->contentInfo->id, $content->getVersionInfo()->versionNo, $content->getVersionInfo()->languageCodes[0] ),
                    false,
                    array( 'site_access' => $siteAccess->name ) + $params
                );

                return ezpEvent::getInstance()->filter( 'response/output', $moduleResult['content'] );
            },
            false
        );

        $legacyHelper->loadDataFromModuleResult( $moduleResult );

        return $result;
    }
}
