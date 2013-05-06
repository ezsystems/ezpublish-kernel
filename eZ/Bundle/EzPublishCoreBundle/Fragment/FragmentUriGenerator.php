<?php
/**
 * File containing the FragmentUriGenerator class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * FragmentUriGenerator is used from fragment renderers (for sub-requests), when a controller reference is used.
 * e.g.:
 * {{ render_esi( controller( 'ez_content:viewLocation', {'locationId': 123} ) ) }}
 *
 * It adds request attributes we want to keep trace of in the controller reference.
 *
 * @see Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer
 */
class FragmentUriGenerator
{
    public function generateFragmentUri( ControllerReference $reference, Request $request )
    {
        // Serialize the siteaccess to get it back after.
        // @see eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener
        if ( $request->attributes->has( 'siteaccess' ) )
            $reference->attributes['serialized_siteaccess'] = serialize( $request->attributes->get( 'siteaccess' ) );

        if ( $request->attributes->has( 'semanticPathinfo' ) )
            $reference->attributes['semanticPathinfo'] = $request->attributes->get( 'semanticPathinfo' );

        if ( $request->attributes->has( 'viewParametersString' ) )
            $reference->attributes['viewParametersString'] = $request->attributes->get( 'viewParametersString' );
    }
}
