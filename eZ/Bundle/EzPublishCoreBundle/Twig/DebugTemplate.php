<?php
/**
 * File containing the DebugTemplate class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Twig;

use eZ\Bundle\EzPublishCoreBundle\Collector\DebugKernel;

abstract class DebugTemplate extends \Twig_Template
{
    public function display( array $context, array $blocks = array() )
    {
        $startTime = microtime( true );
        parent::display( $context, $blocks );
        $endTime = microtime( true );

        DebugKernel::addTemplate( $this->getTemplateName(), $endTime - $startTime );
    }

}
