<?php
/**
 * File containing the View block matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher\MultipleValued;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

class View extends MultipleValued
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return boolean
     */
    public function matchBlock( Block $block )
    {
        return isset( $this->values[$block->view] );
    }
}
