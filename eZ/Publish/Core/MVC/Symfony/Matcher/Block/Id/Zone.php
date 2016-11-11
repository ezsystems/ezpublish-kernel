<?php

/**
 * File containing the Block id matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Block\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\Block\MultipleValued;
use eZ\Publish\Core\FieldType\Page\Parts\Block as PageBlock;
use eZ\Publish\Core\MVC\Symfony\View\BlockValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Zone extends MultipleValued
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return bool
     */
    public function matchBlock(PageBlock $block)
    {
        return isset($this->values[$block->zoneId]);
    }

    public function match(View $view)
    {
        if (!$view instanceof BlockValueView) {
            return false;
        }

        return isset($this->values[$view->getBlock()->zoneId]);
    }
}
