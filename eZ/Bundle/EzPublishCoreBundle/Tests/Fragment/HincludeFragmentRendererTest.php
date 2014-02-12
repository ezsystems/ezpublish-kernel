<?php
/**
 * File containing the HincludeFragmentRendererTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Fragment;

use eZ\Bundle\EzPublishCoreBundle\Fragment\HincludeFragmentRenderer;

class HincludeFragmentRendererTest extends EsiFragmentRendererTest
{
    protected function getFragmentRenderer()
    {
        return new HincludeFragmentRenderer( $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' ) );
    }
}
