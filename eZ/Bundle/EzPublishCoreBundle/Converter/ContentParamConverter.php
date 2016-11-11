<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Converter;

use eZ\Publish\API\Repository\ContentService;

class ContentParamConverter extends RepositoryParamConverter
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    protected function getSupportedClass()
    {
        return 'eZ\Publish\API\Repository\Values\Content\Content';
    }

    protected function getPropertyName()
    {
        return 'contentId';
    }

    protected function loadValueObject($id)
    {
        return $this->contentService->loadContent($id);
    }
}
