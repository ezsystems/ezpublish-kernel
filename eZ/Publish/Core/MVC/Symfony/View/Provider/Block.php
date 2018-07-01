<?php

/**
 * File containing the View\Provider\Block interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\FieldType\Page\Parts\Block as PageBlock;

/**
 * Interface for block view providers.
 *
 * Block view providers select a view for a given page block, depending on its own internal rules.
 * Such provider is meant to work along with Page field type.
 *
 * @deprecated since 6.0.0
 */
interface Block
{
    /**
     * Returns a ContentView object corresponding to $block, or null if not applicable.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView(PageBlock $block);
}
