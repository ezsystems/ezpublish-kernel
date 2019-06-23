<?php

/**
 * File containing the PageService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\PageService as BasePageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

class PageService extends BasePageService
{
    /**
     * Returns valid block items as content objects.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo[]
     */
    public function getValidBlockItemsAsContentInfo(Block $block)
    {
        $contentInfoObjects = [];
        foreach ($this->getValidBlockItems($block) as $item) {
            try {
                $contentInfoObjects[] = $this->contentService->loadContentInfo($item->contentId);
            } catch (UnauthorizedException $e) {
                // If unauthorized, disregard block as "valid" and continue loading other blocks.
            }
        }

        return $contentInfoObjects;
    }
}
