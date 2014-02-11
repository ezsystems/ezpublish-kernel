<?php
/**
 * File containing the EsiFragmentRenderer class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer as BaseRenderer;

class EsiFragmentRenderer extends BaseRenderer implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    /**
     * @var FragmentUriGenerator
     */
    private $fragmentUriGenerator;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->siteAccess = $siteAccess;
    }

    public function setFragmentPath( $path )
    {
        if ( $this->siteAccess && $this->siteAccess->matcher instanceof SiteAccess\URILexer )
        {
            $path = $this->siteAccess->matcher->analyseLink( $path );
        }

        parent::setFragmentPath( $path );
    }

    protected function generateFragmentUri( ControllerReference $reference, Request $request, $absolute = false )
    {
        if ( !isset( $this->fragmentUriGenerator ) )
        {
            $this->fragmentUriGenerator = new FragmentUriGenerator;
        }

        $this->fragmentUriGenerator->generateFragmentUri( $reference, $request, $absolute );
        return parent::generateFragmentUri( $reference, $request, $absolute );
    }
}
