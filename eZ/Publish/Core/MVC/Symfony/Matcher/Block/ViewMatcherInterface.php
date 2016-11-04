<?php

/**
 * File containing the MatcherInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Block;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

/**
 * Main interface for block matchers.
 */
interface ViewMatcherInterface extends MatcherInterface
{
    /**
     * Checks if a Block object matches.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return bool
     */
    public function matchBlock(Block $block);
}
