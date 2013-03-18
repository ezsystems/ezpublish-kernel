<?php
/**
 * File containing the abstract Gateway class for Page field type.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\PageStorage;

use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

/**
 * Main abstract storage gateway for Page field type.
 */
abstract class Gateway extends StorageGateway
{
    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    abstract public function getValidBlockItems( Block $block );

    /**
     * Returns the block item having a highest visible date, for given block.
     * Will return null if no block item is registered for block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item|null
     */
    abstract public function getLastValidBlockItem( Block $block );

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    abstract public function getWaitingBlockItems( Block $block );

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    abstract public function getArchivedBlockItems( Block $block );
}
