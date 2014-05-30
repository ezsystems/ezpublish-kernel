<?php
/**
 * File containing the WebHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Kernel;

use Symfony\Component\HttpFoundation\Request;
use eZURI;

/**
 * URIFixer is a helper class you can use to "align" legacy eZURI against current Symfony Request object.
 * i.e. Inject correct Pathinfo that has been already processed within Symfony (with SiteAccess detection).
 */
class URIHelper
{
    /**
     * Fixes up legacy eZURI against current request.
     *
     * @param Request $request
     */
    public function updateLegacyURI( Request $request )
    {
        $viewParametersString = rtrim(
            $request->attributes->get(
                'semanticPathinfo',
                $request->getPathinfo()
            ) . $request->attributes->get( 'viewParametersString' ),
            '/'
        );

        $uri = eZURI::instance();
        $uri->setURIString( $viewParametersString );
    }
}
