<?php
/**
 * File containing the Block id matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\Block\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\Block\MultipleValued;
use eZ\Publish\Core\FieldType\Page\Parts\Block as PageBlock;

class Zone extends MultipleValued
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return boolean
     */
    public function matchBlock( PageBlock $block )
    {
        return isset( $this->values[$block->zoneId] );
    }
}
