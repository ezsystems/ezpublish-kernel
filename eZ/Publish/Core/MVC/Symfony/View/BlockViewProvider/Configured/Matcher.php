<?php
/**
 * File containing the Matcher interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured;

use eZ\Publish\Core\MVC\Symfony\View\ViewProviderMatcher;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

/**
 * Main interface for block matchers to be used with View\Provider\Block\Configured.
 */
interface Matcher extends ViewProviderMatcher
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return boolean
     */
    public function matchBlock( Block $block );
}
