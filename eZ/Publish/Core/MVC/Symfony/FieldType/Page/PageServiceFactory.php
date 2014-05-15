<?php
/**
 * File containing the PageServiceFactory class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
