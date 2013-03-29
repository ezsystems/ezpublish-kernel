<?php
/**
 * File containing the PageServiceFactory class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Page;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway as PageGateway;

class PageServiceFactory
{
    /**
     * Builds the page service
     *
     * @param string $serviceClass the class of the page service
     * @param ConfigResolverInterface $resolver
     * @param \eZ\Publish\Core\FieldType\Page\PageStorage\Gateway $storageGateway
     *
     * @return \eZ\Publish\Core\FieldType\Page\PageService
     */
    public function buildService( $serviceClass, ConfigResolverInterface $resolver, PageGateway $storageGateway )
    {
        $pageSettings = $resolver->getParameter( 'ezpage' );
        /** @var $pageService \eZ\Publish\Core\FieldType\Page\PageService */
        $pageService = new $serviceClass( $pageSettings['layouts'], $pageSettings['blocks'] );
        $pageService->setStorageGateway( $storageGateway );
        return $pageService;
    }

}
