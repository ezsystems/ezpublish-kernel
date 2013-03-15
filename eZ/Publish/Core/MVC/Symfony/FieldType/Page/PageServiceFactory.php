<?php
/**
 * File containing the PageServiceFactory class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Page;

use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class PageServiceFactory
{
    /**
     * Builds the page service
     *
     * @param string $serviceClass the class of the page service
     * @param ConfigResolverInterface $resolver
     * @return an instance of $serviceClass
     */
    public function buildService( $serviceClass, ConfigResolverInterface $resolver )
    {
        $pageSettings = $resolver->getParameter( 'ezpage' );
        return new $serviceClass( $pageSettings['layouts'], $pageSettings['blocks'] );
    }

}
