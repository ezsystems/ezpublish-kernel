<?php

/**
 * File containing the PageServiceFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Page;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway as PageGateway;
use eZ\Publish\API\Repository\ContentService;

class PageServiceFactory
{
    /**
     * Builds the page service.
     *
     * @param string $serviceClass the class of the page service
     * @param ConfigResolverInterface $resolver
     * @param \eZ\Publish\Core\FieldType\Page\PageStorage\Gateway $storageGateway
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     *
     * @return \eZ\Publish\Core\FieldType\Page\PageService
     */
    public function buildService(
        $serviceClass,
        ConfigResolverInterface $resolver,
        PageGateway $storageGateway,
        ContentService $contentService
    ) {
        $pageSettings = $resolver->getParameter('ezpage');
        /** @var $pageService \eZ\Publish\Core\FieldType\Page\PageService */
        $pageService = new $serviceClass(
            $contentService,
            $pageSettings['layouts'],
            $pageSettings['blocks']
        );
        $pageService->setStorageGateway($storageGateway);

        return $pageService;
    }
}
