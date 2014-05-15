<?php
/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\PreviewController as BasePreviewController;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;

class PreviewController extends BasePreviewController
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function setConfigResolver( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }

    protected function getForwardRequest( Location $location, Content $content, SiteAccess $previewSiteAccess )
    {
        $request = parent::getForwardRequest( $location, $content, $previewSiteAccess );
        // If the preview siteaccess is configured in legacy_mode, we forward to the LegacyKernelController.
        if ( $this->configResolver->getParameter( 'legacy_mode', 'ezsettings', $previewSiteAccess->name ) )
        {
            $request->attributes->set( '_controller', 'ezpublish_legacy.controller:indexAction' );
        }

        return $request;
    }
}
