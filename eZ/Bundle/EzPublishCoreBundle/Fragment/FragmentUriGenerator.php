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

class FragmentUriGenerator
{
    public function generateFragmentUri( ControllerReference $reference, Request $request )
    {
        if ( $request->attributes->has( 'siteaccess' ) )
            $reference->attributes['siteaccess'] = serialize( $request->attributes->get( 'siteaccess' ) );
    }
}
