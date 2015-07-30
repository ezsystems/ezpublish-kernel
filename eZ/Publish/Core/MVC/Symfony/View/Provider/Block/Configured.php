<?php

/**
 * File containing the View\Provider\Block\Configured class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Block;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as BaseConfigured;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Block as BlockProvider;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

class Configured extends BaseConfigured implements BlockProvider
{
    /**
     * Returns a ContentView object corresponding to $block, or null if not applicable.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView(Block $block)
    {
        $viewConfig = $this->matcherFactory->match($block, 'block');
        if (empty($viewConfig)) {
            return;
        }

        return $this->buildContentView($viewConfig);
    }
}
