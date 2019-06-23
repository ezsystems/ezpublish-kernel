<?php

/**
 * File containing the PageController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService as CoreBundlePageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\Controller\PageController as BasePageController;

/**
 * This controller provides the block view feature.
 *
 * @deprecated since 6.0.0 This specific override of PageController is deprecated since 6.0.0
 *             and in the future, only base PageController (eZ\Publish\Core\MVC\Symfony\Controller\PageController)
 *             will be used.
 */
class PageController extends BasePageController
{
    public function viewBlock(Block $block, array $params = [], array $cacheSettings = [])
    {
        // Inject valid items as ContentInfo objects if possible.
        if ($this->pageService instanceof CoreBundlePageService) {
            $params += [
                'valid_contentinfo_items' => $this->pageService->getValidBlockItemsAsContentInfo($block),
            ];
        }

        return parent::viewBlock($block, $params, $cacheSettings);
    }
}
