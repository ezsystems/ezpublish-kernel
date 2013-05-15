<?php
/**
 * File containing the HincludeFragmentRenderer class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use Symfony\Bundle\FrameworkBundle\Fragment\ContainerAwareHIncludeFragmentRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class HincludeFragmentRenderer extends ContainerAwareHIncludeFragmentRenderer
{
    /**
     * @var FragmentUriGenerator
     */
    private $fragmentUriGenerator;

    protected function generateFragmentUri( ControllerReference $reference, Request $request )
    {
        if ( !isset( $this->fragmentUriGenerator ) )
        {
            $this->fragmentUriGenerator = new FragmentUriGenerator;
        }

        $this->fragmentUriGenerator->generateFragmentUri( $reference, $request );
        return parent::generateFragmentUri( $reference, $request );
    }
}
