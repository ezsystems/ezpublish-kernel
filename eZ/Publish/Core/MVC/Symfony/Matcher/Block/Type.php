<?php

/**
 * File containing the Type block matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\Block;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

class Type extends MultipleValued
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return bool
     */
    public function matchBlock(Block $block)
    {
        return isset($this->values[$block->type]);
    }
}
