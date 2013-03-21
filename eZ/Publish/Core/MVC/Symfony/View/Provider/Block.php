<?php
/**
 * File containing the View\Provider\Block interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\FieldType\Page\Parts\Block as PageBlock;

/**
 * Interface for block view providers.
 *
 * Block view providers select a view for a given page block, depending on its own internal rules.
 * Such provider is meant to work along with Page field type.
 */
interface Block extends ViewProviderInterface
{
    /**
     * Returns a ContentView object corresponding to $block, or null if not applicable
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( PageBlock $block );
}
