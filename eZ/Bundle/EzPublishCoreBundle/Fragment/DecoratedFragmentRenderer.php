<?php
/**
 * File containing the DecoratedFragmentRenderer class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class DecoratedFragmentRenderer implements FragmentRendererInterface, SiteAccessAware
{
    /**
     * @var \Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface
     */
    private $innerRenderer;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    public function __construct( FragmentRendererInterface $innerRenderer )
    {
        $this->innerRenderer = $innerRenderer;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->siteAccess = $siteAccess;
    }

    public function setFragmentPath( $path )
    {
        if ( !$this->innerRenderer instanceof RoutableFragmentRenderer )
        {
            return null;
        }

        if ( $this->siteAccess && $this->siteAccess->matcher instanceof SiteAccess\URILexer )
        {
            $path = $this->siteAccess->matcher->analyseLink( $path );
        }

        $this->innerRenderer->setFragmentPath( $path );
    }

    /**
     * Renders a URI and returns the Response content.
     *
     * @param string|ControllerReference $uri A URI as a string or a ControllerReference instance
     * @param Request $request A Request instance
     * @param array $options An array of options
     *
     * @return Response A Response instance
     */
    public function render( $uri, Request $request, array $options = array() )
    {
        if ( $uri instanceof ControllerReference && $request->attributes->has( 'siteaccess' ) )
        {
            // Serialize the siteaccess to get it back after.
            // @see eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener
            $siteaccess = clone $request->attributes->get( 'siteaccess' );
            if ( !$siteaccess->matcher instanceof URILexer )
            {
                // Do not include matcher in the serialized siteaccess if it does not implement URILexer,
                // which prevents the serialized siteaccess / fragment from becoming too large.
                $siteaccess->matcher = null;
            }
            $uri->attributes['serialized_siteaccess'] = serialize( $siteaccess );
        }

        return $this->innerRenderer->render( $uri, $request, $options );
    }

    /**
     * Gets the name of the strategy.
     *
     * @return string The strategy name
     */
    public function getName()
    {
        return $this->innerRenderer->getName();
    }
}
